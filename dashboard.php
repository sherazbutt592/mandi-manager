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
    $title = "Dashboard";
    include("partials/title-meta.php");
    include("partials/head-css.php");
    ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css">
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

                <!-- Stat Cards -->
                <div class="row">
                    <div class="col-xl-3 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <i class='bx bx-user float-end m-0 h2 text-muted'></i>
                                <h6 class="text-muted text-uppercase mt-0">Total Customers</h6>
                                <h3 id="totalCustomersCount" class="mb-3" data-plugin="counterup">0</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <i class='bx bxs-component float-end m-0 h2 text-muted'></i>
                                <h6 class="text-muted text-uppercase mt-0">Total Items</h6>
                                <h3 id="totalProductsCount" class="mb-3" data-plugin="counterup">0</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <i class="bx bx-dollar-circle float-end m-0 h2 text-muted"></i>
                                <h6 class="text-muted text-uppercase mt-0">Total Sales</h6>
                                <h3 class="mb-3">Rs <span id="monthly-sales" data-plugin="counterup">0</span></h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <i class='bx bx-money-withdraw float-end m-0 h2 text-muted'></i>
                                <h6 class="text-muted text-uppercase mt-0">Due Amount</h6>
                                <h3 class="mb-3" id="total-due">Rs <span data-plugin="counterup">0</span></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row">
                    <div class="col-xl-6" style="width: 30%;">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Current Stock</h4>
                                <div id="morris-donut-example" class="morris-chart" style="height: 260px;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6" style="width: 70%;">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Daily Sales</h4>
                                <div id="morris-bar-example" class="morris-chart" style="height: 260px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alerts Row -->
                <div class="row">
                    <!-- Loan Alerts -->
                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title overflow-hidden">Loans Alerts!</h4>
                                <div class="table-responsive">
                                    <table class="table table-centered table-hover table-xl mb-0" id="loan-alerts-table">
                                        <thead>
                                            <tr>
                                                <th>Customers</th>
                                                <th>Phone</th>
                                                <th>Amount</th>
                                                <th>Due Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr><td colspan="4">Loading...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stock Alerts -->
                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title overflow-hidden">Stock Alerts!</h4>
                                <div class="table-responsive">
                                    <table class="table table-centered table-hover table-xl mb-0" id="stock-alerts">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Quantity</th>
                                                <th>Expiry Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr><td colspan="3">Loading...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <?php include("partials/footer.php"); ?>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.3.0/raphael.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js"></script>
    <script src="assets/js/dashboard.js"></script>

</body>

</html>
