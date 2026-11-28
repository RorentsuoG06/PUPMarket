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

<script type="module" src="assets/js/nexconn-chat.bundle.js?v=5"></script>
