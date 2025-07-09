<?php
ini_set('display_errors', 0); // Don't show errors to user
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');
error_reporting(E_ALL);

include("connection.php");

// Add notification to the database
function addNotification($conn, $title, $message, $icon = 'mdi mdi-bell', $link = null) {
    $stmt = $conn->prepare("
        INSERT INTO notifications (title, message, icon, link, is_read, created_at)
        VALUES (?, ?, ?, ?, 0, NOW())
    ");
    if ($stmt) {
        $stmt->bind_param("ssss", $title, $message, $icon, $link);
        $stmt->execute();
        $stmt->close();
    }
}

// Return JSON response and exit
function json_response($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ADD PRODUCT
    if ($action === 'add') {
        $name = trim($_POST['name']);
        $serial_no = trim($_POST['serial_no']);
        $quantity = trim($_POST['quantity']);
        $date_added = $_POST['date_added'];
        $expiry_date = $_POST['expiry_date'];

        if (empty($name) || empty($serial_no) || empty($quantity) || empty($date_added) || empty($expiry_date)) {
            json_response(["status" => "missing_fields"]);
        }

        if (!is_numeric($quantity)) {
            json_response(["status" => "invalid_quantity"]);
        }

        $quantity = intval($quantity);

        $query = "INSERT INTO products (name, serial_no, quantity, date_added, expiry_date)
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            json_response(["status" => "prepare_failed"]);
        }

        $stmt->bind_param("ssiss", $name, $serial_no, $quantity, $date_added, $expiry_date);

        if ($stmt->execute()) {
            $inserted_id = $stmt->insert_id;

            // Low stock notification
            if ($quantity < 10) {
                $title = "Low Stock Alert";
                $msg = "Stock for '$name / $serial_no' is below 10 units.";
                addNotification($conn, $title, $msg, 'mdi mdi-cube-outline', "stock.php");
            }

            // Expiry notification
            $expiry_timestamp = strtotime($expiry_date);
            $now = strtotime(date('Y-m-d'));
            $days_until_expiry = ($expiry_timestamp - $now) / (60 * 60 * 24);

            if ($days_until_expiry >= 0 && $days_until_expiry <= 5) {
                $title = "Expiry Alert";
                $msg = "'$name / $serial_no' will expire on $expiry_date.";
                addNotification($conn, $title, $msg, 'mdi mdi-timer-sand', "stock.php");
            }

            // Auto-delete if quantity is 0
            if ($quantity === 0) {
                $deleteStmt = $conn->prepare("DELETE FROM products WHERE serial_no = ?");
                if ($deleteStmt) {
                    $deleteStmt->bind_param("s", $serial_no);
                    $deleteStmt->execute();
                    $deleteStmt->close();
                    json_response(["status" => "deleted_due_to_zero_quantity"]);
                } else {
                    json_response(["status" => "success_but_delete_failed", "id" => $inserted_id]);
                }
            } else {
                json_response(["status" => "success", "id" => $inserted_id]);
            }
        } else {
            json_response(["status" => "db_error", "error" => $stmt->error]);
        }

        $stmt->close();
        $conn->close();
        exit;
    }

    // FETCH PRODUCTS
    elseif ($action === 'fetch') {
        $query = "SELECT id, name, serial_no, quantity, date_added, expiry_date FROM products";
        $result = $conn->query($query);

        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }

        json_response(["data" => $products]);
    }

    // DELETE PRODUCT
    elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';

        if (empty($id) || !is_numeric($id)) {
            json_response(["status" => "missing_or_invalid_id"]);
        }

        $query = "DELETE FROM products WHERE id = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            json_response(["status" => "prepare_failed"]);
        }

        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            json_response(["status" => "success"]);
        } else {
            json_response(["status" => "db_error", "error" => $stmt->error]);
        }

        $stmt->close();
        $conn->close();
        exit;
    }

    // UPDATE PRODUCT
    elseif ($action === 'update') {
        $name = trim($_POST['name']);
        $serial_no = trim($_POST['serial_no']);
        $serial_no_original = trim($_POST['serial_no_original']);
        $quantity = trim($_POST['quantity']);
        $date_added = $_POST['date_added'] ?? null;
        $expiry_date = $_POST['expiry_date'];

        if (empty($name) || empty($serial_no) || empty($serial_no_original) || empty($quantity) || empty($expiry_date)) {
            json_response(["status" => "missing_fields"]);
        }

        if (!is_numeric($quantity)) {
            json_response(["status" => "invalid_quantity"]);
        }

        $quantity = intval($quantity);

        if ($date_added) {
            $query = "UPDATE products 
                      SET name = ?, serial_no = ?, quantity = ?, date_added = ?, expiry_date = ? 
                      WHERE serial_no = ?";
            $stmt = $conn->prepare($query);

            if (!$stmt) {
                json_response(["status" => "prepare_failed"]);
            }

            $stmt->bind_param("ssisss", $name, $serial_no, $quantity, $date_added, $expiry_date, $serial_no_original);
        } else {
            $query = "UPDATE products 
                      SET name = ?, serial_no = ?, quantity = ?, expiry_date = ? 
                      WHERE serial_no = ?";
            $stmt = $conn->prepare($query);

            if (!$stmt) {
                json_response(["status" => "prepare_failed"]);
            }

            $stmt->bind_param("ssiss", $name, $serial_no, $quantity, $expiry_date, $serial_no_original);
        }

        if ($stmt->execute()) {
            // Low stock notification
            if ($quantity < 10) {
                $title = "Low Stock Alert";
                $msg = "Stock for '$name / $serial_no' is critically low: $quantity unit(s) remaining.";
                addNotification($conn, $title, $msg, 'mdi mdi-cube-outline', "stock.php");
            }

            // Expiry notification
            $expiry_timestamp = strtotime($expiry_date);
            $now = strtotime(date('Y-m-d'));
            $days_until_expiry = ($expiry_timestamp - $now) / (60 * 60 * 24);

            if ($days_until_expiry >= 0 && $days_until_expiry <= 5) {
                $title = "Expiry Alert";
                $msg = "'$name / $serial_no' will expire on $expiry_date.";
                addNotification($conn, $title, $msg, 'mdi mdi-timer-sand');
            }

            json_response(["status" => "success"]);
        } else {
            json_response(["status" => "db_error", "error" => $stmt->error]);
        }

        $stmt->close();
        $conn->close();
        exit;
    }

    // INVALID ACTION
    else {
        json_response(["status" => "invalid_action"]);
    }
}

// INVALID REQUEST METHOD
else {
    json_response(["status" => "invalid_request"]);
}
