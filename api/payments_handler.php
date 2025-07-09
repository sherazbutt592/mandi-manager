<?php
ob_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');
date_default_timezone_set('Asia/Karachi');

header('Content-Type: application/json');
include("connection.php");

function addNotification($conn, $title, $message, $icon = 'mdi mdi-bell', $link = null) {
    $stmt = $conn->prepare("INSERT INTO notifications (title, message, icon, link, is_read, created_at) VALUES (?, ?, ?, ?, 0, NOW())");
    if ($stmt) {
        $stmt->bind_param("ssss", $title, $message, $icon, $link);
        $stmt->execute();
        $stmt->close();
    }
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $type = $_GET['type'] ?? '';
    $result = $conn->query("SELECT * FROM due_transactions ORDER BY transaction_time DESC");
    $payments = [];

    while ($row = $result->fetch_assoc()) {
        $formatted_items = '';
        $items_array = explode(';', $row['items']);
        foreach ($items_array as $item) {
            $item = trim($item);
            if ($item === '') continue;
            if (preg_match('/^(.*?)\s*\(Qty:\s*(\d+),\s*Rate:\s*(\d+),/i', $item, $matches)) {
                $name = trim($matches[1]);
                $qty = $matches[2];
                $rate = $matches[3];
                $formatted_items .= "$name ($qty x $rate)<br>";
            } else {
                $formatted_items .= "$item<br>";
            }
        }
        $row['formatted_items'] = $formatted_items;
        $payments[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $payments]);
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $required = ['invoice_no', 'customer_name', 'paid', 'next_payment_date', 'payment_method'];
    foreach ($required as $field) {
        if (!isset($data[$field]) || $data[$field] === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Missing field: $field"]);
            exit;
        }
    }

    $invoice_no = $data['invoice_no'];
    $additional_paid = floatval($data['paid']);

    $stmt = $conn->prepare("SELECT * FROM due_transactions WHERE invoice_no = ?");
    $stmt->bind_param("s", $invoice_no);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $total = floatval($row['total']);
        $previous_paid = floatval($row['paid']);
        $items = $row['items'];
        $new_paid = $previous_paid + $additional_paid;
        $new_due = $total - $new_paid;

        if ($new_due < 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Payment exceeds due amount.']);
            exit;
        }

        $stmt->close();

        $stmt = $conn->prepare("UPDATE due_transactions SET 
            customer_name = ?, 
            paid = ?, 
            due = ?, 
            next_payment_date = ?, 
            payment_method = ? 
            WHERE invoice_no = ?");
        $stmt->bind_param("sddsss", $data['customer_name'], $new_paid, $new_due, $data['next_payment_date'], $data['payment_method'], $invoice_no);

        if ($stmt->execute()) {
            $stmt->close();

            $stmt = $conn->prepare("SELECT * FROM due_transactions WHERE invoice_no = ?");
            $stmt->bind_param("s", $invoice_no);
            $stmt->execute();
            $result = $stmt->get_result();
            $record = $result->fetch_assoc();
            $stmt->close();

            if (!$record || empty($record['invoice_no'])) {
                error_log("Invalid or missing invoice_no");
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Transaction not found or invoice missing']);
                exit;
            }

            $customerStmt = $conn->prepare("SELECT id FROM customers WHERE phone = ?");
            $customerStmt->bind_param("s", $record['customer_phone']);
            $customerStmt->execute();
            $customerResult = $customerStmt->get_result();
            $customerRow = $customerResult->fetch_assoc();
            $customerStmt->close();

            $customer_id = $customerRow ? $customerRow['id'] : null;

            $formatted_items = '';
            $items_array = explode(';', $record['items']);
            foreach ($items_array as $item) {
                $item = trim($item);
                if ($item === '') continue;
                if (preg_match('/^(.*?)\s*\(Qty:\s*(\d+),\s*Rate:\s*(\d+),/i', $item, $matches)) {
                    $name = trim($matches[1]);
                    $qty = $matches[2];
                    $rate = $matches[3];
                    $formatted_items .= "$name ($qty x $rate)<br>";
                } else {
                    $formatted_items .= "$item<br>";
                }
            }

            $insertStmt = $conn->prepare("INSERT INTO transaction_history 
                (customer_id, invoice_number, total, paid, due_amount, payment_method, next_payment_date, items, transaction_time)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $insertStmt->bind_param(
                "isdddssss",
                $customer_id,
                $record['invoice_no'],
                $record['total'],
                $record['paid'],
                $record['due'],
                $record['payment_method'],
                $record['next_payment_date'],
                $formatted_items,
                date('Y-m-d H:i:s')
            );

            if ($insertStmt->execute()) {
                // Notify if due in 3 days
                if (!empty($record['next_payment_date'])) {
                    $nextDate = new DateTime($record['next_payment_date']);
                    $now = new DateTime();
                    $daysLeft = (int)$now->diff($nextDate)->format('%r%a');

                    if ($daysLeft >= 0 && $daysLeft <= 3 && $new_due > 0) {
                        $title = "Upcoming Payment";
                        $message = "{$record['customer_name']} has a due payment on {$record['next_payment_date']}.";
                        addNotification($conn, $title, $message, 'mdi mdi-calendar-clock', "payments.php");
                    }
                }

                // Credit score logic
                if ($customer_id) {
                    $due_date = !empty($record['next_payment_date']) ? new DateTime($record['next_payment_date']) : null;
                    $payment_date = new DateTime();
                    $scoreChange = 0;

                    if ($due_date instanceof DateTime) {
                        if ($payment_date <= $due_date) {
                            $scoreChange = ($new_due <= 0) ? 10 : 5;
                        } else {
                            $scoreChange = ($new_due <= 0) ? -10 : 0;
                        }
                    }

                    $currentScore = 100;
                    $scoreStmt = $conn->prepare("SELECT credit_score FROM customers WHERE id = ?");
                    $scoreStmt->bind_param("i", $customer_id);
                    $scoreStmt->execute();
                    $scoreResult = $scoreStmt->get_result();
                    if ($row = $scoreResult->fetch_assoc()) {
                        $currentScore = intval($row['credit_score']);
                    }
                    $scoreStmt->close();

                    $newScore = max(0, min(100, $currentScore + $scoreChange));
                    $updateStmt = $conn->prepare("UPDATE customers SET credit_score = ? WHERE id = ?");
                    $updateStmt->bind_param("ii", $newScore, $customer_id);
                    $updateStmt->execute();
                    $updateStmt->close();
                }

                echo json_encode(['success' => true, 'message' => 'Payment updated and logged in transaction history']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to insert into transaction history']);
            }

            $insertStmt->close();
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update payment']);
        }
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Transaction not found']);
    }

    exit;
}

if ($method === 'DELETE') {
    parse_str(file_get_contents("php://input"), $deleteData);
    $invoice_no = $deleteData['invoice_no'] ?? '';

    if (empty($invoice_no)) {
        echo json_encode(['success' => false, 'message' => 'Missing invoice_no']);
        exit;
    }

    $selectStmt = $conn->prepare("SELECT * FROM due_transactions WHERE invoice_no = ?");
    $selectStmt->bind_param("s", $invoice_no);
    $selectStmt->execute();
    $result = $selectStmt->get_result();
    $record = $result->fetch_assoc();
    $selectStmt->close();

    if (!$record) {
        echo json_encode(['success' => false, 'message' => 'Record not found']);
        exit;
    }

    $formatted_items = '';
    $items_array = explode(';', $record['items']);
    foreach ($items_array as $item) {
        $item = trim($item);
        if ($item === '') continue;
        if (preg_match('/^(.*?)\s*\(Qty:\s*(\d+),\s*Rate:\s*(\d+),/i', $item, $matches)) {
            $name = trim($matches[1]);
            $qty = $matches[2];
            $rate = $matches[3];
            $formatted_items .= "$name ($qty x $rate)<br>";
        } else {
            $formatted_items .= "$item<br>";
        }
    }

    $customerStmt = $conn->prepare("SELECT id FROM customers WHERE phone = ?");
    $customerStmt->bind_param("s", $record['customer_phone']);
    $customerStmt->execute();
    $customerResult = $customerStmt->get_result();
    $customerRow = $customerResult->fetch_assoc();
    $customerStmt->close();

    if (!$customerRow) {
        echo json_encode(['success' => false, 'message' => 'Customer not found']);
        exit;
    }

    $customer_id = $customerRow['id'];
    $total = floatval($record['total']);
    $paid = $total;
    $due = 0;

    $insertStmt = $conn->prepare("INSERT INTO transaction_history 
        (customer_id, invoice_number, total, paid, due_amount, payment_method, next_payment_date, items, transaction_time)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $next_payment_date = null;
    $insertStmt->bind_param(
        "isdddssss",
        $customer_id,
        $record['invoice_no'],
        $total,
        $paid,
        $due,
        $record['payment_method'],
        $next_payment_date,
        $formatted_items,
        date('Y-m-d H:i:s')
    );

    if (!$insertStmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Failed to archive transaction: ' . $conn->error]);
        exit;
    }
    $insertStmt->close();

    if ($customer_id) {
        $scoreStmt = $conn->prepare("SELECT credit_score FROM customers WHERE id = ?");
        $scoreStmt->bind_param("i", $customer_id);
        $scoreStmt->execute();
        $scoreResult = $scoreStmt->get_result();
        $currentScore = 100;
        if ($scoreRow = $scoreResult->fetch_assoc()) {
            $currentScore = intval($scoreRow['credit_score']);
        }
        $scoreStmt->close();

        $scoreChange = 10;
        $newScore = max(0, min(100, $currentScore + $scoreChange));

        $updateScore = $conn->prepare("UPDATE customers SET credit_score = ? WHERE id = ?");
        $updateScore->bind_param("ii", $newScore, $customer_id);
        $updateScore->execute();
        $updateScore->close();
    }

    $deleteStmt = $conn->prepare("DELETE FROM due_transactions WHERE invoice_no = ?");
    $deleteStmt->bind_param("s", $invoice_no);
    if ($deleteStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Payment cleared, record archived and deleted']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete: ' . $conn->error]);
    }
    $deleteStmt->close();

    exit;
}

ob_end_clean();
http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed']);
exit;
?>
