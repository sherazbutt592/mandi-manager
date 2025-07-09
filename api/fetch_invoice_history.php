<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /mandi_manager/user_login.php");
    exit();
}

include("connection.php");

// Handle DELETE request first to avoid conflicts with SELECT statement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['action']) && $data['action'] === 'delete') {
        if (!isset($data['id']) || !is_numeric($data['id'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid invoice ID']);
            exit();
        }
        $invoiceId = intval($data['id']);

        $stmtDelete = $conn->prepare("DELETE FROM transaction_history WHERE id = ?");
        if (!$stmtDelete) {
            echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
            exit();
        }
        $stmtDelete->bind_param("i", $invoiceId);
        $resultDelete = $stmtDelete->execute();

        if ($resultDelete) {
            echo json_encode(['success' => true, 'message' => 'Invoice deleted']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete invoice']);
        }

        $stmtDelete->close();
        $conn->close();
        exit();
    }
}

// Fetch start_date and end_date from GET; default to empty strings if missing
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

if ($startDate && $endDate) {
    $sql = "SELECT 
                th.id, 
                th.customer_id,  
                th.invoice_number, 
                th.transaction_time, 
                c.name AS customer_name, 
                th.items, 
                th.total, 
                th.paid, 
                th.due_amount, 
                th.next_payment_date 
            FROM transaction_history th
LEFT JOIN customers c ON th.customer_id = c.id
            WHERE DATE(th.transaction_time) BETWEEN ? AND ? 
            ORDER BY th.transaction_time DESC";

    $stmtSelect = $conn->prepare($sql);
    if (!$stmtSelect) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        exit();
    }
    $stmtSelect->bind_param("ss", $startDate, $endDate);
    $stmtSelect->execute();
} else {
    $sql = "SELECT 
                th.id, 
                th.customer_id,  
                th.invoice_number, 
                th.transaction_time, 
                c.name AS customer_name, 
                th.items, 
                th.total, 
                th.paid, 
                th.due_amount, 
                th.next_payment_date 
     FROM transaction_history th
LEFT JOIN customers c ON th.customer_id = c.id
            ORDER BY th.transaction_time DESC";

    $stmtSelect = $conn->prepare($sql);
    if (!$stmtSelect) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        exit();
    }
}

$stmtSelect->execute();
$result = $stmtSelect->get_result();

$invoices = [];
while ($row = $result->fetch_assoc()) {

    $row['customer_name'] = $row['customer_name'] ?? 'Deleted Customer';
    // Decode JSON items into readable string
    $items = json_decode($row['items'], true);
    if (is_array($items)) {
        $itemDetails = array_map(function ($item) {
            $name = $item['item'] ?? '';
            $qty = $item['quantity'] ?? ($item['qty'] ?? '');
            $rate = $item['rate'] ?? ''; // Assuming the rate is included in the item
            return ($name !== '') ? "$name: ($qty x $rate)" : ''; // Include item name with quantity x rate
        }, $items);
        $row['items'] = implode('<br>', array_filter($itemDetails)); // Join item details with <br>
    }
    $invoices[] = $row;
}

$stmtSelect->close();
$conn->close();

echo json_encode(['success' => true, 'data' => $invoices]);
exit;
