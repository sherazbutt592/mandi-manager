<?php
include("connection.php");

// Helper function to send JSON and exit
function send_json($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Handle POST request (Add, Edit, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['name']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        $credit_score = intval($_POST['credit_score']);

        if (empty($name) || empty($phone) || empty($address)) {
            echo "missing_fields";
            exit;
        }

        $query = "INSERT INTO customers (name, phone, address, credit_score) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            echo "prepare_failed";
            exit;
        }

        $stmt->bind_param("sssi", $name, $phone, $address, $credit_score);
        echo $stmt->execute() ? "success" : "db_error: " . $stmt->error;

        $stmt->close();
        $conn->close();
        exit;
    }

    elseif ($action === 'edit') {
        $id = intval($_POST['id']);
        $name = trim($_POST['name']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);

        if (empty($name) || empty($phone) || empty($address)) {
            echo "missing_fields";
            exit;
        }

        $query = "UPDATE customers SET name = ?, phone = ?, address = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            echo "prepare_failed";
            exit;
        }

        $stmt->bind_param("sssi", $name, $phone, $address, $id);
        echo $stmt->execute() ? "success" : "db_error: " . $stmt->error;

        $stmt->close();
        $conn->close();
        exit;
    }

    elseif ($action === 'delete') {
        $id = intval($_POST['id']);
        $query = "DELETE FROM customers WHERE id = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            echo "prepare_failed";
            exit;
        }

        $stmt->bind_param("i", $id);
        echo $stmt->execute() ? "success" : "db_error: " . $stmt->error;

        $stmt->close();
        $conn->close();
        exit;
    }

    else {
        echo "invalid_action";
        exit;
    }
}

// Handle GET request to fetch all customers or single customer or count
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';

    if ($action === 'fetch') {
        $query = "SELECT id, name, phone, address, credit_score FROM customers";
        $result = $conn->query($query);

        if (!$result) {
            echo "db_error: " . $conn->error;
            exit;
        }

        $customers = [];
        while ($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }

        send_json(['data' => $customers]);
    }

    elseif ($action === 'fetchById' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $query = "SELECT id, name, phone, address, credit_score FROM customers WHERE id = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            echo "prepare_failed";
            exit;
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $customer = $result->fetch_assoc();
            send_json($customer);
        } else {
            echo "customer_not_found";
        }

        $stmt->close();
        $conn->close();
        exit;
    }

    else {
        echo "invalid_action";
        exit;
    }
}

// Log the action for debugging (only if POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log('Action: ' . ($_POST['action'] ?? ''));
}
