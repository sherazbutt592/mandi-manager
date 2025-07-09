<?php
ini_set('display_errors', 0); // Turn OFF output of warnings/errors
ini_set('log_errors', 1);     // Log errors instead
ini_set('error_log', __DIR__ . '/error_log.txt'); // Save logs to this file
header('Content-Type: application/json');
include("connection.php");

$month = isset($_GET['month']) ? intval($_GET['month']) : 0;
$year = isset($_GET['year']) ? intval($_GET['year']) : 0;

if ($month < 1 || $month > 12 || $year < 2000) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid month or year"]);
    exit;
}

$start_date = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
$end_date = date("Y-m-t", strtotime($start_date)); // last date of the month

$sql = "SELECT sale_date AS date, SUM(total_amount) AS amount
        FROM sales
        WHERE sale_date BETWEEN ? AND ?
        GROUP BY sale_date
        ORDER BY sale_date ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        "date" => $row['date'],
        "amount" => (float)$row['amount']
    ];
}

echo json_encode($data);
