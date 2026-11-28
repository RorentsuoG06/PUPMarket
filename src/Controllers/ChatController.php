<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;

class ChatController extends Controller
{
    public function index(): void
    {
        Session::requireAuth("login.php");

        $productId = filter_input(INPUT_GET, "product", FILTER_VALIDATE_INT);
        if ($productId === false || $productId < 1) {
            $productId = null;
        }

        $this->view("pages/chats", [
            "pageTitle" => "Chats | PUPMarket",
            "currentPage" => "chats",
            "csrfToken" => Session::csrfToken(),
            "productId" => $productId,
        ]);
    }
}
