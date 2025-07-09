<?php
include("connection.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $passwordRaw = isset($_POST['password']) ? $_POST['password'] : '';

    if ($name && $email && $passwordRaw) {
        $password = password_hash($passwordRaw, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");

        if ($stmt) {
            $stmt->bind_param("sss", $name, $email, $password);

            if ($stmt->execute()) {
                header("Location: /mandi_manager/user_login.php?register=success");
                exit();
            } else {
                echo "<script>alert('Registration failed: " . htmlspecialchars($stmt->error) . "'); window.history.back();</script>";
            }
        } else {
            echo "<script>alert('Database error: " . htmlspecialchars($conn->error) . "'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('All fields are required.'); window.history.back();</script>";
    }
}
?>

