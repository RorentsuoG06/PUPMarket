<?php
require_once __DIR__ . "/../src/Middleware/auth.php";
ensureSessionStarted();
restoreRememberedSession();

if (isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

$message = "";
$messageType = "";

$fullName = $_SESSION["register_old"]["full_name"] ?? "";
$email = $_SESSION["register_old"]["email"] ?? "";
unset($_SESSION["register_old"]);

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
} elseif ($status === "otp_required") {
    $message = "Please enter the 6-digit verification code sent to your email.";
    $messageType = "danger";
} elseif ($status === "otp_expired") {
    $message = "Your verification code has expired or was not found. Please request a new one.";
    $messageType = "danger";
} elseif ($status === "otp_invalid") {
    $message = "The verification code you entered is incorrect.";
    $messageType = "danger";
} elseif ($status === "otp_attempts") {
    $message = "Too many incorrect attempts. Please request a new verification code.";
    $messageType = "danger";
} elseif ($status === "failed") {
    $message = "Something went wrong while creating the account.";
    $messageType = "danger";
}

$pageTitle = "Register | IskoFinds";
$currentPage = "";
$showOtpBox = in_array($status, ["otp_required", "otp_expired", "otp_invalid", "otp_attempts"], true);

require __DIR__ . "/../src/includes/header.php";
require __DIR__ . "/../src/includes/navbar.php";
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
                        <h2 class="fw-bold">Join IskoFinds</h2>
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

                        <div class="otp-preview mb-4<?= $showOtpBox ? '' : ' d-none' ?>" id="otpVerificationBox">
                            <div class="d-flex align-items-start gap-3">
                                <span class="otp-preview-icon">
                                    <i class="bi bi-patch-check-fill"></i>
                                </span>
                                <div>
                                    <h3>Email verification</h3>
                                    <p class="mb-3">
                                        Enter the 6-digit verification code sent to your PUP Webmail.
                                    </p>
                                    <div class="input-group">
                                        <input type="text"
                                               class="form-control"
                                               id="otpCode"
                                               name="otp"
                                               placeholder="6-digit OTP"
                                               inputmode="numeric"
                                               autocomplete="one-time-code"
                                               pattern="[0-9]{6}"
                                               maxlength="6"
                                               <?= $showOtpBox ? "required" : "" ?>
                                               aria-label="One-time password">
                                        <button class="btn btn-outline-pup"
                                                type="button"
                                                id="sendOtpBtn">
                                            <?= $showOtpBox ? "Resend OTP" : "Send OTP" ?>
                                        </button>
                                    </div>
                                    <div id="otpStatus" class="form-text mt-2" aria-live="polite"></div>
                                </div>
                            </div>
                        </div>

                        <button type="submit"
                                class="btn btn-pup auth-submit w-100"
                                id="registerSubmitBtn">

                            <i class="bi bi-person-check-fill"></i>
                            <?= $showOtpBox ? "Verify & Register Account" : "Register Account" ?>

                        </button>

                    </form>

                    <div class="auth-divider my-3">
                        <span>or</span>
                    </div>

                    <a href="actions/microsoft_login.php" class="btn btn-microsoft w-100">
                        <svg width="18" height="18" viewBox="0 0 21 21" aria-hidden="true">
                            <rect x="1" y="1" width="9" height="9" fill="#f25022"/>
                            <rect x="11" y="1" width="9" height="9" fill="#7fba00"/>
                            <rect x="1" y="11" width="9" height="9" fill="#00a4ef"/>
                            <rect x="11" y="11" width="9" height="9" fill="#ffb900"/>
                        </svg>
                        Continue with Microsoft
                    </a>

                    <p class="text-center mt-4 mb-0">
                        Already have an account?
                        <a href="login.php">Log in</a>
                    </p>

                </div>

            </div>

        </div>

    </div>

</section>

<?php require __DIR__ . "/../src/includes/footer.php"; ?>
