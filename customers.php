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
    $title = "Customers";
    include("partials/title-meta.php");
    include("partials/head-css.php");
    ?>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
</head>

<body>
    <div class="layout-wrapper">

        <!-- Sidebar -->
        <?php include("partials/navbar.php"); ?>

        <div class="page-content">

            <!-- Topbar -->
            <?php include("partials/topbar.php"); ?>

            <div class="px-3">
                <?php include("partials/page-title.php"); ?>

            <div class="table-responsive mt-4">
                        <button id="addCustomerBtn" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                        Add Customer
                    </button>

                    <!-- Customers Table -->
                  
                    <table id="datatable-buttons" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Address</th>
                                <th>Credit Score</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Dynamic rows inserted by JS -->
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>

            <!-- Add Customer Modal -->
            <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addCustomerModalLabel">Add New Customer</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="addCustomerForm">
                                <div class="mb-3">
                                    <label for="customerName" class="form-label">Customer Name</label>
                                    <input type="text" class="form-control" id="customerName" required>
                                </div>
                                <div class="mb-3">
                                    <label for="customerPhone" class="form-label">Customer Phone</label>
                                    <input type="number" class="form-control" id="customerPhone" required>
                                </div>
                                <div class="mb-3">
                                    <label for="customerAddress" class="form-label">Customer Address</label>
                                    <textarea class="form-control" id="customerAddress" required></textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" id="saveCustomerBtn">Save Customer</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Customer Modal -->
            <div class="modal fade" id="editCustomerModal" tabindex="-1" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editCustomerModalLabel">Edit Customer Information</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="editCustomerForm">
                                <input type="hidden" id="editCustomerId">
                                <div class="mb-3">
                                    <label for="editCustomerName" class="form-label">Customer Name</label>
                                    <input type="text" class="form-control" id="editCustomerName" required>
                                </div>
                                <div class="mb-3">
                                    <label for="editCustomerPhone" class="form-label">Customer Phone</label>
                                    <input type="number" class="form-control" id="editCustomerPhone" required>
                                </div>
                                <div class="mb-3">
                                    <label for="editCustomerAddress" class="form-label">Customer Address</label>
                                    <textarea class="form-control" id="editCustomerAddress" required></textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" id="saveEditCustomerBtn">Save Changes</button>
                        </div>
                    </div>
                </div>
            </div>

        </div> <!-- end page-content -->
    </div> <!-- end layout-wrapper -->

   <!-- JS & Dependencies -->
    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.js"></script>

    <!-- DataTables core -->
    <script src="assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>

    <!-- Responsive extension -->
    <script src="assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
    <script src="assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js"></script>

    <!-- Buttons extension -->
    <script src="assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script src="assets/libs/datatables.net-buttons-bs5/js/buttons.bootstrap5.min.js"></script>
    <script src="assets/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>
    <script src="assets/libs/datatables.net-buttons/js/buttons.flash.min.js"></script>
    <script src="assets/libs/datatables.net-buttons/js/buttons.print.min.js"></script>

    <!-- KeyTable & Select -->
    <script src="assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js"></script>
    <script src="assets/libs/datatables.net-select/js/dataTables.select.min.js"></script>

    <!-- PDF export support -->
    <script src="assets/libs/pdfmake/build/pdfmake.min.js"></script>
    <script src="assets/libs/pdfmake/build/vfs_fonts.js"></script>

    <!-- Custom Scripts -->
    <script src="assets/js/datatables.js"></script>
    <script src="assets/js/customers.js"></script>

</body>

</html>
