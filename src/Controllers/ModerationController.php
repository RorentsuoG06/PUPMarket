<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;

class ModerationController extends Controller
{
    public function index(): void
    {
        Session::requireAuth("login.php");
        Session::requireAdmin();

        $this->view("pages/moderation", [
            "pageTitle" => "Moderation | PUPMarket",
            "currentPage" => "moderation",
            "csrfToken" => Session::csrfToken(),
        ]);
    }
}
