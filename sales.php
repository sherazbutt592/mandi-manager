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
    $title = "Manage Sales";
    include("partials/title-meta.php");
    include("partials/head-css.php");
    ?>
    <!-- Morris.js Chart CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css">
</head>

<body>
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

                <!-- Chart and Filters -->
                <div class="row">
                    <div class="card" dir="ltr">
                        <div class="card-body">


                            <!-- Filters: Year Selector & Month Buttons -->
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 mt-4">

                                <!-- Year Selector -->
                                <div>
                                    <label for="yearSelector" class="form-label me-2">Select Year:</label>
                                    <select id="yearSelector" class="form-select d-inline-block w-auto">
                                        <!-- Years will be populated by JS -->
                                    </select>
                                </div>

                                <!-- Month Buttons -->
                                <div class="month-scroll-wrapper mb-3">
                                    <div class="btn-group d-flex flex-wrap" role="group" aria-label="Month Selector">
                                        <button type="button" class="btn btn-outline-primary month-btn" data-month="1">Jan</button>
                                        <button type="button" class="btn btn-outline-primary month-btn" data-month="2">Feb</button>
                                        <button type="button" class="btn btn-outline-primary month-btn" data-month="3">Mar</button>
                                        <button type="button" class="btn btn-outline-primary month-btn" data-month="4">Apr</button>
                                        <button type="button" class="btn btn-outline-primary month-btn" data-month="5">May</button>
                                        <button type="button" class="btn btn-outline-primary month-btn" data-month="6">Jun</button>
                                        <button type="button" class="btn btn-outline-primary month-btn" data-month="7">Jul</button>
                                        <button type="button" class="btn btn-outline-primary month-btn" data-month="8">Aug</button>
                                        <button type="button" class="btn btn-outline-primary month-btn" data-month="9">Sep</button>
                                        <button type="button" class="btn btn-outline-primary month-btn" data-month="10">Oct</button>
                                        <button type="button" class="btn btn-outline-primary month-btn" data-month="11">Nov</button>
                                        <button type="button" class="btn btn-outline-primary month-btn" data-month="12">Dec</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Chart Container -->
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <div id="morris-line-example" style="height: 350px;"></div>
                                </div>
                            </div>
                            <div class="mt-3 text-start">
    <button id="generateReportBtn" class="btn btn-primary">
        <i class="mdi mdi-file-document-outline"></i> Generate Report
    </button>
</div>

                        </div> <!-- end card-body -->
                    </div> <!-- end card -->
                </div> <!-- end row -->

            </div> <!-- end px-3 -->
        </div> <!-- end page-content -->
    </div> <!-- end layout-wrapper -->

    <!-- Scripts -->
    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.js"></script>

    <!-- jQuery (required for Morris.js) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Morris.js and Raphael.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.3.0/raphael.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js"></script>

    <!-- Custom Sales Script -->
    <script src="assets/js/sales.js"></script>
</body>

</html>
