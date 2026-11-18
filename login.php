<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

$message = "";
$messageType = "";

$status = $_GET["status"] ?? "";

if (isset($_GET["registered"])) {
    $message = "Registration successful. You can now log in.";
    $messageType = "success";
} elseif (isset($_GET["logout"])) {
    $message = "You have been signed out.";
    $messageType = "success";
} elseif ($status === "empty") {
    $message = "Please enter your username and password.";
    $messageType = "danger";
} elseif ($status === "invalid") {
    $message = "Invalid username/email or password.";
    $messageType = "danger";
} elseif ($status === "failed") {
    $message = "Something went wrong while logging in.";
    $messageType = "danger";
}

$pageTitle = "Login | PUPMarket";
$currentPage = "";

require "includes/header.php";
require "includes/navbar.php";
?>

<section class="page-header">

    <div class="container text-center">

        <h1 class="fw-bold">
            Login
        </h1>

        <p>
            Log in to your PUPMarket account.
        </p>

    </div>

</section>


<section class="py-5">

    <div class="container">

        <div class="row justify-content-center">

            <div class="col-lg-6">

                <div class="contact-form-card">

                    <h3 class="fw-bold mb-4">
                        Welcome Back
                    </h3>

                    <?php if ($message !== ""): ?>

                        <div class="alert alert-<?= htmlspecialchars($messageType) ?>">
                            <?= htmlspecialchars($message) ?>
                        </div>

                    <?php endif; ?>


                    <form method="POST"
                          action="actions/process_login.php">

                        <div class="mb-3">

                            <label class="form-label">
                                Username or Email
                            </label>

                            <input type="text"
                                   class="form-control"
                                   name="username"
                                   required>

                        </div>


                        <div class="mb-4">

                            <label class="form-label">
                                Password
                            </label>

                            <input type="password"
                                   class="form-control"
                                   name="password"
                                   required>

                        </div>


                        <button type="submit"
                                class="btn btn-pup w-100">

                            <i class="bi bi-box-arrow-in-right"></i>
                            Login

                        </button>

                    </form>


                    <p class="text-center mt-4 mb-0">

                        Don't have an account?
                        <a href="register.php">
                            Register here
                        </a>

                    </p>

                </div>

            </div>

        </div>

    </div>

</section>

<?php require "includes/footer.php"; ?>
