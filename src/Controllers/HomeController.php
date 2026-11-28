<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

class HomeController extends Controller
{
    public function index(): void
    {
        $showLoginAnimation = !empty($_SESSION["login_success"]);
        unset($_SESSION["login_success"]);

        $this->view("pages/home", [
            "pageTitle" => "PUPMarket | PUP Biñan Student Marketplace",
            "currentPage" => "home",
            "showLoginAnimation" => $showLoginAnimation,
        ]);
    }
}
