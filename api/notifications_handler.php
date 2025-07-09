<?php
include("connection.php");

// Set response type to JSON
header('Content-Type: application/json');

// Detect request method
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // ✅ FETCH notifications
    $query = "SELECT * FROM notifications ORDER BY created_at DESC LIMIT 20";
    $result = $conn->query($query);

    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }

    echo json_encode($notifications);
    exit;
}

if ($method === 'POST') {
    // Delete single or all notifications
    $id = $_POST['id'] ?? null;
    $clearAll = $_POST['clear_all'] ?? null;

    if ($clearAll === '1') {
        $conn->query("DELETE FROM notifications");
        echo json_encode(['status' => 'success', 'cleared' => true]);
        exit;
    }

    if ($id) {
        $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo json_encode(['status' => 'success']);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>
