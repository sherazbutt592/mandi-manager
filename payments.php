<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /mandi_manager/user_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light" data-menu-color="dark" data-topbar-color="light">

<head>
    <?php
    $title = "Manage Payments";
    include("partials/title-meta.php");
    include("partials/head-css.php");
    ?>
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
</head>

<body>

    <!-- Begin page -->
    <div class="layout-wrapper">

        <!-- Sidebar -->
        <?php include("partials/navbar.php"); ?>

        <!-- Page Content -->
        <div class="page-content">

            <!-- Topbar -->
            <?php include("partials/topbar.php"); ?>

            <div class="px-3">
                <!-- Page Title -->
                <?php include("partials/page-title.php"); ?>

                <!-- Payments Table -->
                <div class="table-responsive mt-4">
                    <table id="datatable-buttons" class="table table-bordered table-striped nowrap w-100">
                        <thead>
                            <tr>
                                <th>Sr.</th>
                                <th>Invoice No</th>
                                <th>Customer Name</th>
                                <th>Customer Phone</th>
                                <th>Invoice Time</th>
                                <th>Item</th>
                                <th>Total</th>
                                <th>Paid</th>
                                <th>Due</th>
                                <th>Next Payment Date</th>
                                <th>Payment Method</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="invoice-table-body">
                            <!-- Rows will be populated by JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Edit Payment Modal -->
            <div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title" id="addPaymentModalLabel">Edit Payment</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <form id="editPaymentForm">
                                <div class="mb-3">
                                    <label for="invoiceNo" class="form-label">Invoice No</label>
                                    <input type="text" class="form-control" id="invoiceNo" required readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="customername" class="form-label">Customer Name</label>
                                    <input type="text" class="form-control" id="customername" required readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="dueamount" class="form-label">Due Amount</label>
                                    <input type="number" class="form-control" id="dueamount" required readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="payment" class="form-label">Payment</label>
                                    <input type="number" class="form-control" id="payment" required>
                                </div>
                                <div class="mb-3">
                                    <label for="remaining" class="form-label">Remaining</label>
                                    <input type="number" class="form-control" id="remaining" required readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="nextpaymentdate" class="form-label">Next Payment Date</label>
                                    <input type="date" class="form-control" id="nextpaymentdate">
                                </div>
                            </form>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" id="saveEditPaymentBtn">Save Changes</button>
                        </div>

                    </div>
                </div>
            </div>

        </div> <!-- end page-content -->
    </div> <!-- end layout-wrapper -->

    <!-- Vendor Scripts -->
    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.js"></script>

    <!-- DataTables Scripts -->
    <script src="assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
    <script src="assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
    <script src="assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js"></script>
    <script src="assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script src="assets/libs/datatables.net-buttons-bs5/js/buttons.bootstrap5.min.js"></script>
    <script src="assets/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>
    <script src="assets/libs/datatables.net-buttons/js/buttons.flash.min.js"></script>
    <script src="assets/libs/datatables.net-buttons/js/buttons.print.min.js"></script>
    <script src="assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js"></script>
    <script src="assets/libs/datatables.net-select/js/dataTables.select.min.js"></script>
    <script src="assets/libs/pdfmake/build/pdfmake.min.js"></script>
    <script src="assets/libs/pdfmake/build/vfs_fonts.js"></script>

    <!-- Custom Scripts -->
    <script src="assets/js/datatables.js"></script>
    <script src="assets/js/invoice.js"></script>
    <script src="assets/js/payments.js"></script>

</body>

</html>
