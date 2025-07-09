<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: /mandi_manager/dashboard.php");
    exit();
}
?>

<?php
if (isset($_GET['register']) && $_GET['register'] === 'success') {
    echo "<script>alert('Registration successful. Please log in!');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    $title = "Login";
    include("partials/title-meta.php");
    include("partials/head-css.php");
    ?>
</head>

<body class="bg-primary d-flex justify-content-center align-items-center min-vh-100 p-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-4 col-md-5">
                <div class="card">
                    <div class="card-body p-4">

                        <!-- Logo -->
                        <div class="text-center w-75 mx-auto auth-logo mb-4">
                            <a href="#" class="logo-light">
                                <span><img src="assets/images/logo-light.png" alt="Logo" height="50"></span>
                            </a>
                        </div>

                        <!-- Login Form -->
                        <form action="api/login.php" method="POST">
                            <div class="form-group mb-3">
                                <label for="email">Email</label>
                                <input class="form-control" type="email" id="email" name="email" required placeholder="Enter your email">
                            </div>

                            <div class="form-group mb-3">
                                <label for="password">Password</label>
                                <input class="form-control" type="password" id="password" name="password" required placeholder="Enter your password">
                            </div>

                            <div class="form-group mb-0 text-center">
                                <button class="btn btn-primary w-100" type="submit">Log In</button>
                            </div>
                        </form>

                    </div>
                </div>

                <!-- Registration Prompt -->
                <div class="text-center mt-3 text-white-50">
                    <p>Don't have an account? <a href="registeration.php" class="text-white">Sign Up</a></p>
                </div>

            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>

</html>
