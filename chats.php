<?php
require_once __DIR__ . "/../src/Middleware/auth.php";
ensureSessionStarted();
requireAuth("login.php");

$productId = filter_input(INPUT_GET, "product", FILTER_VALIDATE_INT);
if ($productId === false || $productId < 1) {
    $productId = null;
}

$pageTitle = "Chats | PUPMarket";
$currentPage = "chats";
$csrfToken = csrfToken();

require __DIR__ . "/../src/includes/header.php";
require __DIR__ . "/../src/includes/navbar.php";
?>

<section class="page-header">
    <div class="container">
        <h1 class="fw-bold mb-2">Your Chats</h1>
        <p>Message PUP Biñan students about their marketplace listings.</p>
    </div>
</section>

<main class="py-5">
    <div class="container">
        <div class="chat-panel">
            <p id="nexconnChatStatus"
               class="chat-status"
               role="status"
               aria-live="polite"
               data-state="loading">Connecting to Nexconn…</p>
            <p id="nexconnUnreadSummary"
               class="chat-unread-summary"
               role="status"
               aria-live="polite">Loading unread conversations…</p>
            <div id="nexconnChatApp"
                 class="nexconn-chat-app"
                 data-csrf-token="<?= htmlspecialchars($csrfToken) ?>"
                 data-product-id="<?= $productId === null ? "" : (int) $productId ?>"></div>
        </div>
    </div>
</main>

<script type="module" src="js/nexconn-chat.bundle.js?v=5"></script>
<?php require __DIR__ . "/../src/includes/footer.php"; ?>
