<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;

class AuthController extends Controller
{
    public function login(): void
    {
        Session::guestOnly("index.php");

        $status = $_GET["status"] ?? "";
        $message = "";
        $messageType = "";

        if (isset($_GET["registered"])) {
            $message = "Registration successful. You can now log in.";
            $messageType = "success";
        } elseif (isset($_GET["logout"])) {
            $message = "You have been signed out.";
            $messageType = "success";
        } elseif ($status === "password_reset") {
            $message = "Your password has been reset. You can now log in.";
            $messageType = "success";
        } elseif ($status === "empty") {
            $message = "Please enter your PUP webmail and password.";
            $messageType = "danger";
        } elseif ($status === "email") {
            $message = "Use your @iskolarngbayan.pup.edu.ph email address.";
            $messageType = "danger";
        } elseif ($status === "invalid") {
            $message = "The email or password is incorrect.";
            $messageType = "danger";
        } elseif ($status === "oauth_unconfigured") {
            $message = "Microsoft sign-in is not configured yet. Set MICROSOFT_CLIENT_ID and MICROSOFT_CLIENT_SECRET in .env.";
            $messageType = "warning";
        } elseif ($status === "oauth_failed") {
            $message = "Microsoft sign-in could not be completed. Please try again.";
            $messageType = "danger";
        } elseif ($status === "failed") {
            $message = "Something went wrong while logging in.";
            $messageType = "danger";
        } elseif ($status === "remember_failed") {
            $message = "We couldn't enable Remember Me. Please try again or sign in without it.";
            $messageType = "danger";
        }

        $this->view("auth/login", [
            "pageTitle" => "Login | IskoFinds",
            "currentPage" => "",
            "message" => $message,
            "messageType" => $messageType,
        ]);
    }

    public function register(): void
    {
        Session::guestOnly("index.php");

        $fullName = $_SESSION["register_old"]["full_name"] ?? "";
        $email = $_SESSION["register_old"]["email"] ?? "";
        unset($_SESSION["register_old"]);

        $status = $_GET["status"] ?? "";
        $message = "";
        $messageType = "";

        $statusMap = [
            "empty" => "Please fill in all required fields.",
            "email" => "Use a valid @iskolarngbayan.pup.edu.ph email address.",
            "password" => "Password must be at least 6 characters.",
            "match" => "Passwords do not match.",
            "exists" => "That email is already registered.",
            "otp_required" => "Please enter the 6-digit verification code sent to your email.",
            "otp_expired" => "Your verification code has expired or was not found. Please request a new one.",
            "otp_invalid" => "The verification code you entered is incorrect.",
            "otp_attempts" => "Too many incorrect attempts. Please request a new verification code.",
            "failed" => "Something went wrong while creating the account.",
        ];

        if (isset($statusMap[$status])) {
            $message = $statusMap[$status];
            $messageType = "danger";
        }

        $showOtpBox = in_array($status, ["otp_required", "otp_expired", "otp_invalid", "otp_attempts"], true);

        $this->view("auth/register", [
            "pageTitle" => "Register | IskoFinds",
            "currentPage" => "",
            "fullName" => $fullName,
            "email" => $email,
            "message" => $message,
            "messageType" => $messageType,
            "showOtpBox" => $showOtpBox,
        ]);
    }

    public function forgotPassword(): void
    {
        Session::guestOnly("index.php");

        $email = $_SESSION["reset_old_email"] ?? "";
        unset($_SESSION["reset_old_email"]);

        $status = $_GET["status"] ?? "";
        $message = "";
        $messageType = "";

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
            "failed" => ["Something went wrong while resetting your password.", "danger"],
        ];

        if (isset($statusMap[$status])) {
            [$message, $messageType] = $statusMap[$status];
        }

        $showResetFields = in_array(
            $status,
            ["otp_required", "otp_expired", "otp_invalid", "otp_attempts", "password", "match"],
            true
        );

        $this->view("auth/forgot_password", [
            "pageTitle" => "Forgot Password | IskoFinds",
            "currentPage" => "",
            "email" => $email,
            "message" => $message,
            "messageType" => $messageType,
            "showResetFields" => $showResetFields,
        ]);
    }

    public function logout(): void
    {
        if (isset($_COOKIE["pupmarket_remember"]) && function_exists("rememberDatabaseConnection")) {
            $connection = rememberDatabaseConnection();
            if (!forgetRememberedLogin($connection)) {
                error_log("Sign-out completed with a remember token that could not be revoked.");
            }
        }

        $_SESSION = [];
        session_destroy();

        $this->redirect("login.php?logout=1");
    }
}
