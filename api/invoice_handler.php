<?php
ob_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');
// Connect to DB
include("connection.php");

// Set content type to JSON
header('Content-Type: application/json; charset=utf-8');

// Get request type
$type = $_GET['type'] ?? '';

// Fetch customers as JSON
if ($type === 'customers') {
    $stmt = $conn->prepare("SELECT id, name, credit_score FROM customers");
    $stmt->execute();
    $result = $stmt->get_result();
    $customers = [];

    while ($row = $result->fetch_assoc()) {
        $customers[] = [
            'id' => $row['id'],
            'name' => htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'),
            'credit_score' => $row['credit_score']
        ];
    }

    echo json_encode(['success' => true, 'data' => $customers]);
    exit;
}

// Fetch products as JSON
if ($type === 'products') {
    $stmt = $conn->prepare("SELECT id, name, serial_no, quantity FROM products");
    $stmt->execute();
    $result = $stmt->get_result();
    $products = [];

    while ($row = $result->fetch_assoc()) {
        $products[] = [
            'id' => $row['id'],
            'name' => htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'),
            'serial' => htmlspecialchars($row['serial_no'], ENT_QUOTES, 'UTF-8'),
            'quantity' => $row['quantity']
        ];
    }

    echo json_encode(['success' => true, 'data' => $products]);
    exit;
}

// Handle invoice submission
// Handle invoice submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
        exit;
    }

    $required = ['customerName', 'invNo', 'total', 'paid', 'dueAmount', 'paymentMethod', 'items'];
    foreach ($required as $field) {
        if (!isset($input[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Missing field: $field"]);
            exit;
        }
    }

    $customerName = $input['customerName'];
    $dueAmount = floatval($input['dueAmount']);
    $items = $input['items']; // <-- ensure this exists

    // Fetch customer ID and phone
    $stmt = $conn->prepare("SELECT id, phone FROM customers WHERE name = ?");
    $stmt->bind_param("s", $customerName);
    $stmt->execute();
    $stmt->bind_result($customerId, $customerPhone);
    $stmt->fetch();
    $stmt->close();

    if (!$customerId || !$customerPhone) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Customer not found']);
        exit;
    }

    // Format items as readable text
    function formatItemsAsText($items) {
        $lines = [];
        foreach ($items as $item) {
            if (empty($item['item'])) continue;
            $line = "{$item['item']} (Qty: {$item['qty']}, Rate: {$item['rate']}, Amount: {$item['amount']})";
            $lines[] = $line;
        }
        return implode("; ", $lines);
    }

$itemsText = formatItemsAsText($input['items']);
$itemsJson = json_encode($input['items'], JSON_UNESCAPED_UNICODE);
    $nextPaymentDate = $input['nextPaymentDate'] ?? null;
    $transactionTime = $input['transactionTime'] ?? date('Y-m-d H:i:s');

    $conn->begin_transaction();

    // Stock validation
    foreach ($items as $item) {
        if (strpos($item['item'], '/') === false) {
            $conn->rollback();
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Invalid item format: {$item['item']}"]);
            $conn->close();
            exit;
        }
        [$productName, $serial] = explode('/', $item['item'], 2);
        $qty = (int)$item['qty'];

        $checkStmt = $conn->prepare("SELECT quantity FROM products WHERE name = ? AND serial_no = ?");
        $checkStmt->bind_param("ss", $productName, $serial);
        $checkStmt->execute();
        $checkStmt->bind_result($availableQty);
        $checkStmt->fetch();
        $checkStmt->close();

        if ($availableQty < $qty) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => "Not enough stock for $productName/$serial. Available: $availableQty"]);
            $conn->close();
            exit;
        }
    }

    // Insert into transaction_history (still using JSON if needed)
    $stmt = $conn->prepare("INSERT INTO transaction_history 
        (customer_id, invoice_number, total, paid, due_amount, payment_method, next_payment_date, items, transaction_time)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "issddssss",
        $customerId,
        $input['invNo'],
        $input['total'],
        $input['paid'],
        $dueAmount,
        $input['paymentMethod'],
        $nextPaymentDate,
        $itemsJson,
        $transactionTime
    );

    if (!$stmt->execute()) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();

    // Deduct stock
    foreach ($items as $item) {
        [$productName, $serial] = explode('/', $item['item'], 2);
        $qty = (int)$item['qty'];

        $updateStmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE name = ? AND serial_no = ?");
        $updateStmt->bind_param("iss", $qty, $productName, $serial);

        if (!$updateStmt->execute()) {
            $conn->rollback();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update product stock.']);
            $updateStmt->close();
            $conn->close();
            exit;
        }
        $updateStmt->close();
    }

    // Insert into due_transactions (use formatted items text!)
    if ($dueAmount > 0) {
        $stmt_due = $conn->prepare("INSERT INTO due_transactions 
            (customer_name, customer_id, customer_phone, invoice_no, total, paid, due, payment_method, next_payment_date, items, transaction_time)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt_due->bind_param(
            "sissddsssss",
            $customerName,
            $customerId,
            $customerPhone,
            $input['invNo'],
            $input['total'],
            $input['paid'],
            $dueAmount,
            $input['paymentMethod'],
            $nextPaymentDate,
            $itemsText,
            $transactionTime
        );

        if (!$stmt_due->execute()) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Failed to insert due transaction: ' . $stmt_due->error]);
            $stmt_due->close();
            $conn->close();
            exit;
        }

        $stmt_due->close();
    }

     if ($customerId && $dueAmount <= 0) {
        $scoreStmt = $conn->prepare("SELECT credit_score FROM customers WHERE id = ?");
        $scoreStmt->bind_param("i", $customerId);
        $scoreStmt->execute();
        $scoreResult = $scoreStmt->get_result();
        $currentScore = 0;

        if ($scoreRow = $scoreResult->fetch_assoc()) {
            $currentScore = intval($scoreRow['credit_score']);
        }
        $scoreStmt->close();

        if ($currentScore < 100) {
            $scoreChange = 10;
            $newScore = min(100, $currentScore + $scoreChange);

            $updateScore = $conn->prepare("UPDATE customers SET credit_score = ? WHERE id = ?");
            $updateScore->bind_param("ii", $newScore, $customerId);
            $updateScore->execute();
            $updateScore->close();

            error_log("Full payment at purchase: Credit score updated for customer $customerId from $currentScore to $newScore");
        }
    }

    // Record sales
    foreach ($items as $item) {
        [$productName, $serial] = explode('/', $item['item'], 2);
        $qty = (int)$item['qty'];
        $price = (float)$item['rate'];
        $amount = $qty * $price;
        $saleDate = date('Y-m-d');

        $productStmt = $conn->prepare("SELECT id FROM products WHERE name = ? AND serial_no = ?");
        $productStmt->bind_param("ss", $productName, $serial);
        $productStmt->execute();
        $productStmt->bind_result($productId);
        $productStmt->fetch();
        $productStmt->close();

        $checkStmt = $conn->prepare("SELECT id FROM sales WHERE product_id = ? AND sale_date = ?");
        $checkStmt->bind_param("is", $productId, $saleDate);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $updateStmt = $conn->prepare("UPDATE sales SET quantity_sold = quantity_sold + ?, total_amount = total_amount + ? WHERE product_id = ? AND sale_date = ?");
            $updateStmt->bind_param("idis", $qty, $amount, $productId, $saleDate);
            $updateStmt->execute();
            $updateStmt->close();
        } else {
            $insertStmt = $conn->prepare("INSERT INTO sales (product_id, sale_date, quantity_sold, total_amount) VALUES (?, ?, ?, ?)");
            $insertStmt->bind_param("isid", $productId, $saleDate, $qty, $amount);
            $insertStmt->execute();
            $insertStmt->close();
        }

        $checkStmt->close();
    }

    $conn->commit();
    $conn->close();
    echo json_encode(['success' => true, 'message' => 'Invoice saved and stock updated']);
    exit;
}


// Invalid request
http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Invalid request']);
exit;
