<?php
require_once __DIR__ . "/../src/Middleware/auth.php";

guestOnly("index.php");

$email = $_SESSION["reset_old_email"] ?? "";
unset($_SESSION["reset_old_email"]);

$message = "";
$messageType = "";
$status = $_GET["status"] ?? "";

$statusMap = [
    "empty" => ["Please fill in all required fields.", "danger"],
    "email" => ["Use a valid @iskolarngbayan.pup.edu.ph email address.", "danger"],
    "not_found" => ["No account is registered with that PUP Webmail.", "danger"],
    "password" => ["New password must be at least 6 characters.", "danger"],
    "match" => ["New passwords do not match.", "danger"],
    "otp_required" => ["Please enter the 6-digit reset code sent to your email.", "danger"],
    "otp_expired" => ["Your reset code has expired or was not found. Please request a new one.", "danger"],
    "otp_invalid" => ["The reset code you entered is incorrect.", "danger"],
    "otp_attempts" => ["Too many incorrect attempts. Please request a new reset code.", "danger"],
    "failed" => ["Something went wrong while resetting your password.", "danger"]
];

if (isset($statusMap[$status])) {
    [$message, $messageType] = $statusMap[$status];
}

$showResetFields = in_array($status, ["otp_required", "otp_expired", "otp_invalid", "otp_attempts", "password", "match"], true);

$pageTitle = "Forgot Password | IskoFinds";
$currentPage = "";

require __DIR__ . "/../src/includes/header.php";
require __DIR__ . "/../src/includes/navbar.php";
?>

<section class="py-5 auth-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-xl-5">
                <div class="contact-form-card auth-card">
                    <div class="auth-heading">
                        <h2 class="fw-bold">Reset Password</h2>
                        <p class="text-muted mb-0">
                            Enter your PUP Webmail to receive a 6-digit password reset code.
                        </p>
                    </div>

                    <?php if ($message !== ""): ?>
                        <div class="alert alert-<?= htmlspecialchars($messageType) ?>">
                            <?= htmlspecialchars($message) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST"
                          action="actions/process_forgot_password.php"
                          id="forgotPasswordForm">

                        <div class="mb-3">
                            <label class="form-label" for="resetEmail">PUP Webmail</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-envelope-fill"></i>
                                </span>
                                <input type="email"
                                       class="form-control"
                                       id="resetEmail"
                                       name="email"
                                       placeholder="name@iskolarngbayan.pup.edu.ph"
                                       value="<?= htmlspecialchars($email) ?>"
                                       pattern=".+@iskolarngbayan\.pup\.edu\.ph"
                                       title="Use your @iskolarngbayan.pup.edu.ph email address."
                                       autocomplete="email"
                                       required>
                            </div>
                        </div>

                        <div id="resetFieldsBox" class="<?= $showResetFields ? "" : "d-none" ?>">
                            <div class="otp-preview mb-3">
                                <div class="d-flex align-items-start gap-3">
                                    <span class="otp-preview-icon">
                                        <i class="bi bi-key-fill"></i>
                                    </span>
                                    <div>
                                        <h3>Reset code</h3>
                                        <p class="mb-3">
                                            Enter the 6-digit code sent to your PUP Webmail.
                                        </p>
                                        <div class="input-group">
                                            <input type="text"
                                                   class="form-control"
                                                   id="resetOtpCode"
                                                   name="otp"
                                                   placeholder="6-digit code"
                                                   inputmode="numeric"
                                                   autocomplete="one-time-code"
                                                   pattern="[0-9]{6}"
                                                   maxlength="6"
                                                   <?= $showResetFields ? "required" : "" ?>
                                                   aria-label="Reset code">
                                            <button class="btn btn-outline-pup"
                                                    type="button"
                                                    id="sendResetOtpBtn">
                                                <?= $showResetFields ? "Resend Code" : "Send Code" ?>
                                            </button>
                                        </div>
                                        <div id="resetOtpStatus" class="form-text mt-2" aria-live="polite"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="resetNewPassword">New Password</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-lock-fill"></i>
                                    </span>
                                    <input type="password"
                                           class="form-control"
                                           id="resetNewPassword"
                                           name="new_password"
                                           placeholder="Minimum 6 characters"
                                           minlength="6"
                                           autocomplete="new-password"
                                           <?= $showResetFields ? "required" : "" ?>>
                                    <button class="btn btn-outline-secondary password-toggle"
                                            type="button"
                                            data-password-toggle="resetNewPassword"
                                            aria-label="Show new password">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label" for="resetConfirmPassword">Confirm New Password</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-shield-lock-fill"></i>
                                    </span>
                                    <input type="password"
                                           class="form-control"
                                           id="resetConfirmPassword"
                                           name="confirm_password"
                                           placeholder="Re-enter new password"
                                           minlength="6"
                                           autocomplete="new-password"
                                           <?= $showResetFields ? "required" : "" ?>>
                                    <button class="btn btn-outline-secondary password-toggle"
                                            type="button"
                                            data-password-toggle="resetConfirmPassword"
                                            aria-label="Show confirm password">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <button type="submit"
                                class="btn btn-pup auth-submit w-100"
                                id="resetSubmitBtn">
                            <i class="bi bi-arrow-repeat"></i>
                            <?= $showResetFields ? "Reset Password" : "Send Reset Code" ?>
                        </button>
                    </form>

                    <p class="text-center mt-4 mb-0">
                        Remembered your password?
                        <a href="login.php">Back to Log In</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . "/../src/includes/footer.php"; ?>
