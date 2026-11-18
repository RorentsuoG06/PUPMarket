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
$studentNumber = "";
$email = "";
$username = "";

$status = $_GET["status"] ?? "";

if ($status === "empty") {
    $message = "Please fill in all required fields.";
    $messageType = "danger";
} elseif ($status === "email") {
    $message = "Please enter a valid email address.";
    $messageType = "danger";
} elseif ($status === "password") {
    $message = "Password must be at least 6 characters.";
    $messageType = "danger";
} elseif ($status === "match") {
    $message = "Passwords do not match.";
    $messageType = "danger";
} elseif ($status === "exists") {
    $message = "Email, username, or student number is already registered.";
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

<section class="page-header">

    <div class="container text-center">

        <h1 class="fw-bold">
            Create an Account
        </h1>

        <p>
            Register as a PUP Biñan student.
        </p>

    </div>

</section>


<section class="py-5">

    <div class="container">

        <div class="row justify-content-center">

            <div class="col-lg-7">

                <div class="contact-form-card">

                    <h3 class="fw-bold mb-4">
                        Sign Up
                    </h3>

                    <?php if ($message !== ""): ?>

                        <div class="alert alert-<?= htmlspecialchars($messageType) ?>">
                            <?= htmlspecialchars($message) ?>
                        </div>

                    <?php endif; ?>


                    <form method="POST"
                          action="actions/process_register.php">

                        <div class="mb-3">

                            <label class="form-label">
                                Full Name
                            </label>

                            <input type="text"
                                   class="form-control"
                                   name="full_name"
                                   value="<?= htmlspecialchars($fullName) ?>"
                                   required>

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                Student Number
                            </label>

                            <input type="text"
                                   class="form-control"
                                   name="student_number"
                                   value="<?= htmlspecialchars($studentNumber) ?>"
                                   required>

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                Email
                            </label>

                            <input type="email"
                                   class="form-control"
                                   name="email"
                                   value="<?= htmlspecialchars($email) ?>"
                                   required>

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                Username
                            </label>

                            <input type="text"
                                   class="form-control"
                                   name="username"
                                   value="<?= htmlspecialchars($username) ?>"
                                   required>

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                Password
                            </label>

                            <input type="password"
                                   class="form-control"
                                   name="password"
                                   required>

                        </div>


                        <div class="mb-4">

                            <label class="form-label">
                                Confirm Password
                            </label>

                            <input type="password"
                                   class="form-control"
                                   name="confirm_password"
                                   required>

                        </div>


                        <button type="submit"
                                class="btn btn-pup w-100">

                            <i class="bi bi-person-plus"></i>
                            Register

                        </button>

                    </form>


                    <p class="text-center mt-4 mb-0">

                        Already have an account?
                        <a href="login.php">
                            Login here
                        </a>

                    </p>

                </div>

            </div>

        </div>

    </div>

</section>

<?php require "includes/footer.php"; ?>
