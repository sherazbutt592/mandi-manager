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
    $title = "Manage Stock";
    include("partials/title-meta.php");
    include("partials/head-css.php");
    ?>
    <!-- ✅ Bootstrap 5 skin for DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

</head>

<body>
    <div class="layout-wrapper">

        <!-- Sidebar -->
        <?php include("partials/navbar.php"); ?>

        <div class="page-content">

            <!-- Topbar -->
            <?php include("partials/topbar.php"); ?>

            <div class="px-3">
                <!-- Page Title -->
                <?php include("partials/page-title.php"); ?>

                <!-- Stock Table and Add Button -->
                <div class="container mt-4" style="width: 100%;">
                    <button id="addProductBtn" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addProductModal">
                        Add Product
                    </button>

                    <table id="datatable-buttons" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Serial No.</th>
                                <th>Quantity</th>
                                <th>Date Added</th>
                                <th>Expiry Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Dynamic rows added via JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Add Product Modal -->
            <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add New Product</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="addProductForm">
                                <div class="mb-3">
                                    <label for="productName" class="form-label">Name</label>
                                    <input type="text" class="form-control" id="productName" required>
                                </div>
                                <div class="mb-3">
                                    <label for="serialNo" class="form-label">Serial No</label>
                                    <input type="text" class="form-control" id="serialNo" required>
                                </div>
                                <div class="mb-3">
                                    <label for="quantity" class="form-label">Quantity</label>
                                    <input type="number" class="form-control" id="quantity" required>
                                </div>
                                <div class="mb-3">
                                    <label for="dateAdded" class="form-label">Date Added</label>
                                    <input type="date" class="form-control" id="dateAdded" required>
                                </div>
                                <div class="mb-3">
                                    <label for="expiryDate" class="form-label">Expiry Date</label>
                                    <input type="date" class="form-control" id="expiryDate" required>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" id="saveAddProductBtn">Save Product</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Product Modal -->
            <div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Product Information</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="editProductForm">
                                <input type="hidden" id="editSerialNoOriginal" />

                                <div class="mb-3">
                                    <label for="editProductId" class="form-label">Product ID</label>
                                    <input type="text" class="form-control" id="editProductId" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="editProductName" class="form-label">Name</label>
                                    <input type="text" class="form-control" id="editProductName" required>
                                </div>
                                <div class="mb-3">
                                    <label for="editSerialNo" class="form-label">Serial No</label>
                                    <input type="text" class="form-control" id="editSerialNo" required>
                                </div>
                                <div class="mb-3">
                                    <label for="editQuantity" class="form-label">Quantity</label>
                                    <input type="number" class="form-control" id="editQuantity" required>
                                </div>
                                <div class="mb-3">
                                    <label for="editExpiryDate" class="form-label">Expiry Date</label>
                                    <input type="date" class="form-control" id="editExpiryDate" required>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" id="saveEditProductBtn">Update Product</button>
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
    <script src="assets/js/stock.js"></script>

</body>

</html>
