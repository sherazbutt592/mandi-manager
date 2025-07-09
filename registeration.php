<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: /mandi_manager/dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light" data-menu-color="dark" data-topbar-color="light">

<head>
    <?php
    $title = "Registration";
    include("partials/title-meta.php");
    include("partials/head-css.php");
    ?>
</head>

<body class="bg-primary d-flex justify-content-center align-items-center min-vh-100 p-5">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-4 col-md-5">

                <!-- Registration Card -->
                <div class="card">
                    <div class="card-body p-4">

                        <!-- Logo -->
                        <div class="text-center w-75 mx-auto auth-logo mb-4">
                            <a href="#" class="logo-light">
                                <span><img src="assets/images/logo-light.png" alt="Logo" height="50"></span>
                            </a>
                        </div>

                        <!-- Registration Form -->
                        <form action="api/register.php" method="POST">
                            <div class="form-group mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input class="form-control" type="text" id="name" name="name" required placeholder="Enter your name">
                            </div>

                            <div class="form-group mb-3">
                                <label for="emailaddress" class="form-label">Email address</label>
                                <input class="form-control" type="email" id="emailaddress" name="email" required placeholder="Enter your email">
                            </div>

                            <div class="form-group mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input class="form-control" type="password" id="password" name="password" required placeholder="Enter your password">
                            </div>

                            <div class="form-group mb-0 text-center">
                                <button class="btn btn-primary w-100" type="submit">Sign Up</button>
                            </div>
                        </form>

                    </div> <!-- end card-body -->
                </div> <!-- end card -->

                <!-- Login Redirect -->
                <div class="row mt-3">
                    <div class="col-12 text-center">
                        <p class="text-white-50">
                            Already have an account?
                            <a href="user_login.php" class="text-white font-weight-medium ms-1">Log In</a>
                        </p>
                    </div>
                </div>

            </div> <!-- end col -->
        </div> <!-- end row -->
    </div> <!-- end container -->

    <!-- Scripts -->
    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.js"></script>
    
</body>

</html>
