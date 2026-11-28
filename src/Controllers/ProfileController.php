<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\User;

class ProfileController extends Controller
{
    public function index(): void
    {
        Session::requireAuth("login.php");

        $userId = Session::userId();
        $userModel = new User();
        $user = $userId !== null ? $userModel->findById($userId) : null;

        if ($user === null) {
            $this->redirect("logout.php");
        }

        $status = $_GET["status"] ?? "";
        $message = "";
        $messageType = "";

        $statusMap = [
            "profile_updated" => ["Profile details updated successfully.", "success"],
            "password_updated" => ["Password changed successfully.", "success"],
            "empty" => ["Full name and username are required.", "danger"],
            "username_invalid" => ["Username must be 3–30 characters (letters, numbers, underscores only).", "danger"],
            "username_taken" => ["That username is already taken.", "danger"],
            "avatar_invalid" => ["Profile photo must be a JPG, PNG, or WEBP image under 2MB.", "danger"],
            "pw_empty" => ["Please fill in all password fields.", "danger"],
            "pw_current" => ["Your current password is incorrect.", "danger"],
            "pw_length" => ["New password must be at least 6 characters.", "danger"],
            "pw_match" => ["New passwords do not match.", "danger"],
            "failed" => ["Something went wrong. Please try again.", "danger"],
        ];

        if (isset($statusMap[$status])) {
            [$message, $messageType] = $statusMap[$status];
        }

        $createdAt = (string) ($user["created_at"] ?? "");
        $memberSince = $createdAt !== "" ? date("F j, Y", strtotime($createdAt)) : "";

        $this->view("pages/profile", [
            "pageTitle" => "My Profile | IskoFinds",
            "currentPage" => "profile",
            "fullName" => (string) ($user["full_name"] ?? ""),
            "email" => (string) ($user["email"] ?? ""),
            "username" => (string) ($user["username"] ?? ""),
            "courseYear" => (string) ($user["course_year"] ?? ""),
            "contactInfo" => (string) ($user["contact_info"] ?? ""),
            "bio" => (string) ($user["bio"] ?? ""),
            "avatarPath" => (string) ($user["avatar_path"] ?? ""),
            "memberSince" => $memberSince,
            "message" => $message,
            "messageType" => $messageType,
        ]);
    }
}
