<?php
declare(strict_types=1);

require_once dirname(__DIR__) . "/config/app.php";

use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\ProductController;
use App\Controllers\AboutController;
use App\Controllers\ContactController;
use App\Controllers\AuthController;
use App\Controllers\ProfileController;
use App\Controllers\ChatController;
use App\Controllers\ModerationController;

$router = new Router();

// Pages
$router->get("/", HomeController::class, "index");
$router->get("/index", HomeController::class, "index");
$router->get("/products", ProductController::class, "index");
$router->get("/my-listings", ProductController::class, "myListings");
$router->get("/my_listings", ProductController::class, "myListings");
$router->get("/about", AboutController::class, "index");
$router->get("/contact", ContactController::class, "index");
$router->post("/contact", ContactController::class, "submit");
$router->get("/profile", ProfileController::class, "index");
$router->get("/chats", ChatController::class, "index");
$router->get("/moderation", ModerationController::class, "index");

// Auth
$router->get("/login", AuthController::class, "login");
$router->get("/register", AuthController::class, "register");
$router->get("/forgot-password", AuthController::class, "forgotPassword");
$router->get("/forgot_password", AuthController::class, "forgotPassword");
$router->get("/logout", AuthController::class, "logout");

$router->dispatch($_SERVER["REQUEST_URI"] ?? "/", $_SERVER["REQUEST_METHOD"] ?? "GET");
