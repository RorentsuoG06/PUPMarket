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
    $message = "Please enter your PUP webmail, student number, and password.";
    $messageType = "danger";
} elseif ($status === "email") {
    $message = "Use your @iskolarngbayan.pup.edu.ph email address.";
    $messageType = "danger";
} elseif ($status === "invalid") {
    $message = "The email, student number, or password is incorrect.";
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

<section class="py-5 auth-page">

    <div class="container">

        <div class="row justify-content-center">

            <div class="col-lg-6 col-xl-5">

                <div class="contact-form-card auth-card">

                    <div class="auth-switch" aria-label="Account options">
                        <span class="active" aria-current="page">
                            <i class="bi bi-box-arrow-in-right"></i>
                            Log In
                        </span>
                        <a href="register.php">
                            <i class="bi bi-person-plus-fill"></i>
                            Create Account
                        </a>
                    </div>

                    <div class="auth-heading">
                        <h2 class="fw-bold">Welcome Back</h2>
                        <p class="text-muted mb-0">Log in with your PUP student credentials.</p>
                    </div>

                    <?php if ($message !== ""): ?>

                        <div class="alert alert-<?= htmlspecialchars($messageType) ?>">
                            <?= htmlspecialchars($message) ?>
                        </div>

                    <?php endif; ?>


                    <form method="POST"
                          action="actions/process_login.php">

                        <div class="mb-3">

                            <label class="form-label" for="loginEmail">
                                PUP Webmail
                            </label>

                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-envelope-fill"></i>
                                </span>
                                <input type="email"
                                       class="form-control"
                                       id="loginEmail"
                                       name="email"
                                       placeholder="name@iskolarngbayan.pup.edu.ph"
                                       pattern=".+@iskolarngbayan\.pup\.edu\.ph"
                                       title="Use your @iskolarngbayan.pup.edu.ph email address."
                                       autocomplete="email"
                                       required>
                            </div>

                        </div>

                        <div class="mb-3">

                            <label class="form-label" for="loginStudentNumber">
                                Student Number
                            </label>

                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-person-vcard-fill"></i>
                                </span>
                                <input type="text"
                                       class="form-control"
                                       id="loginStudentNumber"
                                       name="student_number"
                                       placeholder="Enter your student number"
                                       autocomplete="off"
                                       required>
                            </div>

                        </div>

                        <div class="mb-4">

                            <label class="form-label" for="loginPassword">
                                Password
                            </label>

                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock-fill"></i>
                                </span>
                                <input type="password"
                                       class="form-control"
                                       id="loginPassword"
                                       name="password"
                                       placeholder="Enter your password"
                                       autocomplete="current-password"
                                       required>
                                <button class="btn btn-outline-secondary password-toggle"
                                        type="button"
                                        data-password-toggle="loginPassword"
                                        aria-label="Show password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>

                        </div>


                        <button type="submit"
                                class="btn btn-pup auth-submit w-100">

                            <i class="bi bi-box-arrow-in-right"></i>
                            Log In

                        </button>

                    </form>


                    <p class="text-center mt-4 mb-0">

                        Don't have an account?
                        <a href="register.php">Create an account</a>

                    </p>

                </div>

            </div>

        </div>

    </div>

</section>

<?php require "includes/footer.php"; ?>
