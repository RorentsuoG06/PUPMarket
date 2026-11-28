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

$fullName = "";
$email = "";

$status = $_GET["status"] ?? "";

if ($status === "empty") {
    $message = "Please fill in all required fields.";
    $messageType = "danger";
} elseif ($status === "email") {
    $message = "Use a valid @iskolarngbayan.pup.edu.ph email address.";
    $messageType = "danger";
} elseif ($status === "password") {
    $message = "Password must be at least 6 characters.";
    $messageType = "danger";
} elseif ($status === "match") {
    $message = "Passwords do not match.";
    $messageType = "danger";
} elseif ($status === "exists") {
    $message = "That email is already registered.";
    $messageType = "danger";
} elseif ($status === "failed") {
    $message = "Something went wrong while creating the account.";
    $messageType = "danger";
}

$pageTitle = "Register | PUPMarket";
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
                        <a href="login.php">
                            <i class="bi bi-box-arrow-in-right"></i>
                            Log In
                        </a>
                        <span class="active" aria-current="page">
                            <i class="bi bi-person-plus-fill"></i>
                            Create Account
                        </span>
                    </div>

                    <div class="auth-heading">
                        <h2 class="fw-bold">Join PUPMarket</h2>
                        <p class="text-muted mb-0">Create your student account to get started.</p>
                    </div>

                    <?php if ($message !== ""): ?>

                        <div class="alert alert-<?= htmlspecialchars($messageType) ?>">
                            <?= htmlspecialchars($message) ?>
                        </div>

                    <?php endif; ?>


                    <form method="POST"
                          action="actions/process_register.php"
                          id="registerForm">

                        <div class="mb-3">

                            <label class="form-label" for="fullName">
                                Full Name
                            </label>

                            <input type="text"
                                   class="form-control"
                                   id="fullName"
                                   name="full_name"
                                   placeholder="Enter your full name"
                                   value="<?= htmlspecialchars($fullName) ?>"
                                   required>

                        </div>

                        <div class="mb-3">

                            <label class="form-label" for="registerEmail">
                                Email Address
                            </label>

                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-envelope-fill"></i>
                                </span>
                                <input type="email"
                                       class="form-control"
                                       id="registerEmail"
                                       name="email"
                                       placeholder="e.g. name@iskolarngbayan.pup.edu.ph"
                                       value="<?= htmlspecialchars($email) ?>"
                                       pattern=".+@iskolarngbayan\.pup\.edu\.ph"
                                       title="Use your @iskolarngbayan.pup.edu.ph email address."
                                       autocomplete="email"
                                       required>
                            </div>
                            <div class="form-text auth-email-hint">
                                Must end with <strong>@iskolarngbayan.pup.edu.ph</strong>
                            </div>

                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                Password
                            </label>

                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock-fill"></i>
                                </span>
                                <input type="password"
                                       class="form-control"
                                       id="registerPassword"
                                       name="password"
                                       placeholder="Minimum 6 characters"
                                       minlength="6"
                                       autocomplete="new-password"
                                       required>
                                <button class="btn btn-outline-secondary password-toggle"
                                        type="button"
                                        data-password-toggle="registerPassword"
                                        aria-label="Show password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>

                        </div>

                        <div class="mb-4">

                            <label class="form-label">
                                Confirm Password
                            </label>

                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-shield-lock-fill"></i>
                                </span>
                                <input type="password"
                                       class="form-control"
                                       id="confirmPassword"
                                       name="confirm_password"
                                       placeholder="Re-enter password"
                                       minlength="6"
                                       autocomplete="new-password"
                                       required>
                                <button class="btn btn-outline-secondary password-toggle"
                                        type="button"
                                        data-password-toggle="confirmPassword"
                                        aria-label="Show confirm password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>

                        </div>

                        <div class="otp-preview mb-4">
                            <div class="d-flex align-items-start gap-3">
                                <span class="otp-preview-icon">
                                    <i class="bi bi-patch-check-fill"></i>
                                </span>
                                <div>
                                    <h3>Email verification</h3>
                                    <p class="mb-3">
                                        UI preview only: no code is sent, and entered codes are not verified.
                                    </p>
                                    <div class="input-group">
                                        <input type="text"
                                               class="form-control"
                                               placeholder="6-digit OTP"
                                               inputmode="numeric"
                                               autocomplete="one-time-code"
                                               pattern="[0-9]{6}"
                                               maxlength="6"
                                               required
                                               aria-label="One-time password">
                                        <button class="btn btn-outline-pup"
                                                type="button"
                                                data-otp-preview>
                                            Send OTP
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit"
                                class="btn btn-pup auth-submit w-100">

                            <i class="bi bi-person-check-fill"></i>
                            Register Account

                        </button>

                    </form>

                    <p class="text-center mt-4 mb-0">
                        Already have an account?
                        <a href="login.php">Log in</a>
                    </p>

                </div>

            </div>

        </div>

    </div>

</section>

<?php require "includes/footer.php"; ?>
