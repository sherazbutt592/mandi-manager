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
    $title = "Invoice History";
    include("partials/title-meta.php");
    include("partials/head-css.php");
    ?>
    
    <!-- DataTables Styles -->
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

                <!-- Filter Form -->
                <div class="container mt-4">
                    <form id="filter-form" class="d-flex align-items-center gap-3" action="javascript:void(0);">
                        <div class="d-flex align-items-center gap-2">
                            <label for="start-date" class="form-label mb-0">Start Date:</label>
                            <input type="date" class="form-control" id="start-date" name="start-date" required>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label for="end-date" class="form-label mb-0">End Date:</label>
                            <input type="date" class="form-control" id="end-date" name="end-date" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Search</button>
                    </form>

                    <!-- Result Placeholder -->
                    <div id="result" class="mt-4"></div>
                </div>

                <!-- Invoice Table -->
                <div class="table-responsive mt-4">
                    <table id="datatable-buttons" class="table table-bordered table-striped nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th>Sr.</th>
                                <th>Invoice No</th>
                                <th>Customer ID</th>
                                <th>Customer Name</th>
                                <th>Invoice Time</th>
                                <th>Item</th>
                                <th>Total Amount</th>
                                <th>Amount Paid</th>
                                <th>Due Amount</th>
                                <th>Next Payment Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="invoice-table-body">
                            <!-- Rows will be injected via JS -->
                        </tbody>
                    </table>
                </div>
            </div>

        </div> <!-- end page-content -->
    </div> <!-- end layout-wrapper -->

    <!-- Vendor Scripts -->
    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.js"></script>

    <!-- DataTables Core JS -->
    <script src="assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>

    <!-- Responsive Extension -->
    <script src="assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
    <script src="assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js"></script>

    <!-- Buttons Extension -->
    <script src="assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script src="assets/libs/datatables.net-buttons-bs5/js/buttons.bootstrap5.min.js"></script>
    <script src="assets/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>
    <script src="assets/libs/datatables.net-buttons/js/buttons.flash.min.js"></script>
    <script src="assets/libs/datatables.net-buttons/js/buttons.print.min.js"></script>

    <!-- KeyTable & Select -->
    <script src="assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js"></script>
    <script src="assets/libs/datatables.net-select/js/dataTables.select.min.js"></script>

    <!-- PDFMake for export -->
    <script src="assets/libs/pdfmake/build/pdfmake.min.js"></script>
    <script src="assets/libs/pdfmake/build/vfs_fonts.js"></script>

    <!-- Custom JS -->
    <script src="assets/js/datatables.js"></script>
    <script src="assets/js/history.js"></script>

</body>
</html>
