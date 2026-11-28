<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ContactInquiry;
use Throwable;

class ContactController extends Controller
{
    public function index(): void
    {
        $status = $_GET["status"] ?? "";
        $message = "";
        $messageType = "";

        if ($status === "success") {
            $message = "Your inquiry has been successfully submitted.";
            $messageType = "success";
        } elseif ($status === "empty") {
            $message = "Please fill in all required fields.";
            $messageType = "danger";
        } elseif ($status === "email") {
            $message = "Please enter a valid email address.";
            $messageType = "danger";
        } elseif ($status === "failed") {
            $message = "Something went wrong while saving your inquiry.";
            $messageType = "danger";
        }

        $this->view("pages/contact", [
            "pageTitle" => "Contact | PUPMarket",
            "currentPage" => "contact",
            "message" => $message,
            "messageType" => $messageType,
        ]);
    }

    public function submit(): void
    {
        if (!$this->isPost()) {
            $this->redirect("contact");
        }

        $name = trim($_POST["name"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $subject = trim($_POST["subject"] ?? "");
        $messageText = trim($_POST["message"] ?? "");

        if ($name === "" || $email === "" || $subject === "" || $messageText === "") {
            $this->redirect("contact?status=empty");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirect("contact?status=email");
        }

        try {
            $inquiry = new ContactInquiry();
            $saved = $inquiry->create($name, $email, $subject, $messageText);
            $this->redirect($saved ? "contact?status=success" : "contact?status=failed");
        } catch (Throwable $e) {
            error_log("Contact inquiry failed: " . $e->getMessage());
            $this->redirect("contact?status=failed");
        }
    }
}
