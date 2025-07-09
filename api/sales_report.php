<?php
// sales_report.php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');
include("connection.php");

$month = isset($_GET['month']) ? intval($_GET['month']) : 0;
$year = isset($_GET['year']) ? intval($_GET['year']) : 0;

if ($month < 1 || $month > 12 || $year < 2000) {
    echo "<h3>Invalid month or year</h3>";
    exit;
}

$monthName = date("F", mktime(0, 0, 0, $month, 10));
$start_date = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
$end_date = date("Y-m-t", strtotime($start_date));

// Fetch product-wise sales summary
$sql = "SELECT 
            p.id AS product_id,
            p.name AS product_name,
            p.serial_no,
            SUM(s.quantity_sold) AS total_quantity,
            SUM(s.total_amount) AS total_amount
        FROM sales s
        JOIN products p ON s.product_id = p.id
        WHERE s.sale_date BETWEEN ? AND ?
        GROUP BY p.id, p.name, p.serial_no
        ORDER BY total_amount DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
$grand_total = 0;
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
    $grand_total += $row['total_amount'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sales Report - <?= "$monthName $year" ?></title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background-color: #f4f4f4; }
        .summary { margin-top: 20px; font-size: 18px; text-align: right; }
        .btn-print { margin-top: 20px; text-align: center; }
        @media print {
            .btn-print { display: none; }
        }
    </style>
</head>
<body>

    <h2>Product-wise Sales Report for <?= "$monthName $year" ?></h2>

    <div class="btn-print">
        <button onclick="window.print()">🖨️ Print / Save as PDF</button>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Product ID</th>
                <th>Product Name / Serial No</th>
                <th>Total Quantity Sold</th>
                <th>Total Amount (Rs.)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($rows) === 0): ?>
                <tr><td colspan="5">No sales found for this month.</td></tr>
            <?php else: ?>
                <?php foreach ($rows as $i => $row): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($row['product_id']) ?></td>
                        <td><?= htmlspecialchars($row['product_name']) . " / " . htmlspecialchars($row['serial_no']) ?></td>
                        <td><?= $row['total_quantity'] ?></td>
                        <td><?= number_format($row['total_amount']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <?php if (count($rows) > 0): ?>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align:right;"><strong>Grand Total:</strong></td>
                    <td><strong><?= number_format($grand_total) ?> Rs</strong></td>
                </tr>
            </tfoot>
        <?php endif; ?>
    </table>

</body>
</html>
