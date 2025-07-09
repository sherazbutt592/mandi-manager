<?php
include("connection.php");
header('Content-Type: application/json');

$type = $_GET['type'] ?? '';
$action = $_GET['action'] ?? '';

// Handle Stock Alerts
if ($type === 'stock') {
    $twoDaysLater = date("Y-m-d", strtotime("+2 days"));

    $query = "SELECT name, serial_no, quantity, expiry_date FROM products 
              WHERE expiry_date <= ? 
              ORDER BY expiry_date ASC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $twoDaysLater);
    $stmt->execute();
    $result = $stmt->get_result();

    $alerts = [];
    while ($row = $result->fetch_assoc()) {
        $row['full_name'] = $row['name'] . ' / ' . $row['serial_no'];
        $alerts[] = $row;
    }

    echo json_encode($alerts);

// Handle Loan Alerts
} elseif ($type === 'loan') {
    $tomorrow = date('Y-m-d', strtotime('+1 day'));

    $query = "SELECT c.name AS customer_name, dt.customer_phone, dt.due, dt.next_payment_date
              FROM due_transactions dt
              JOIN customers c ON dt.customer_phone = c.phone
              WHERE dt.next_payment_date = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $tomorrow);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'customer_name' => $row['customer_name'],
            'customer_phone' => $row['customer_phone'],
            'due' => $row['due'],
            'next_payment_date' => $row['next_payment_date'],
        ];
    }

    echo json_encode($data);

// Handle Daily Sales Chart
} elseif ($type === 'sales') {
    $date = date('Y-m-d');

    $sql = "SELECT p.name AS product, p.serial_no, SUM(s.quantity_sold) AS quantity, SUM(s.total_amount) AS amount
            FROM sales s
            JOIN products p ON s.product_id = p.id
            WHERE s.sale_date = ?
            GROUP BY s.product_id";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
        'y' => "{$row['product']} ({$row['serial_no']})",
        'a' => (int)$row['quantity'],
        'b' => (float)$row['amount']
        ];
    }

    echo json_encode($data);

// Handle Monthly Total Sales
} elseif ($type === 'monthly_total') {
    $firstDay = date('Y-m-01');
    $lastDay = date('Y-m-t');

    $stmt = $conn->prepare("SELECT SUM(total_amount) as total_sales FROM sales WHERE sale_date BETWEEN ? AND ?");
    $stmt->bind_param("ss", $firstDay, $lastDay);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $totalSales = $row['total_sales'] ?? 0;

    echo json_encode(['total_sales' => number_format($totalSales)]);

// Handle Total Customers Count
} elseif ($type === 'count') {
    $query = "SELECT COUNT(*) as total_customers FROM customers";
    $result = $conn->query($query);

    if ($result) {
        $row = $result->fetch_assoc();
        echo json_encode(['total_customers' => intval($row['total_customers'])]);
    } else {
        echo json_encode(['error' => 'Database query failed']);
    }

// Handle Product Quantity Chart
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'chart_data') {
    $query = "SELECT name, serial_no, quantity FROM products";
    $result = $conn->query($query);

    $data = [];

    while ($row = $result->fetch_assoc()) {
        $label = $row['name'] . '/' . $row['serial_no']; // Combine name and serial number
        $data[] = [
            "label" => $label,
            "value" => intval($row['quantity'])
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($data);

    $conn->close();
    exit;
}

// Handle Total Stock Quantity
 elseif ($action === 'total_quantity') {
    $query = "SELECT SUM(quantity) as total_quantity FROM products";
    $result = $conn->query($query);

    if ($result) {
        $row = $result->fetch_assoc();
        echo json_encode(['total_quantity' => (int)$row['total_quantity']]);
    } else {
        echo json_encode(['error' => 'Could not fetch total quantity']);
    }
    exit;

// Fallback
}  elseif ($type === 'total_due') {
    $result = $conn->query("SELECT SUM(due) AS total_due FROM due_transactions");

    if ($result && $row = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'total_due' => round($row['total_due'] ?? 0)]);
    } else {
        echo json_encode(['success' => false, 'total_due' => 0]);
    }
    exit;
}
    else {
    echo json_encode(['error' => 'Invalid or missing parameters']);
}
