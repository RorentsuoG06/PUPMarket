<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;

class ProductController extends Controller
{
    public function index(): void
    {
        $this->view("pages/products", [
            "pageTitle" => "Products | PUPMarket",
            "currentPage" => "products",
        ]);
    }

    public function myListings(): void
    {
        Session::requireAuth("login.php");

        $this->view("pages/my_listings", [
            "pageTitle" => "My Listings | PUPMarket",
            "currentPage" => "my_listings",
            "csrfToken" => Session::csrfToken(),
        ]);
    }
}
