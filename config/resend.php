<?php
require_once __DIR__ . "/env.php";

/* Resend API Configuration for IskoFinds OTP Verification */
define("RESEND_API_KEY", env("RESEND_API_KEY", ""));
define("RESEND_FROM_EMAIL", env("RESEND_FROM_EMAIL", "IskoFinds <onboarding@resend.dev>"));
?>
