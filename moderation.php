<?php
require_once __DIR__ . "/../src/Middleware/auth.php";
ensureSessionStarted();
restoreRememberedSession();
requireAuth("login.php");
requireAdmin();

$pageTitle = "Moderation | PUPMarket";
$currentPage = "moderation";
$csrfToken = csrfToken();

require __DIR__ . "/../src/includes/header.php";
require __DIR__ . "/../src/includes/navbar.php";
?>

<section class="page-header">
    <div class="container">
        <h1 class="fw-bold mb-2">Moderation Reports</h1>
        <p>Review reports submitted about marketplace listings and sellers.</p>
    </div>
</section>

<main class="py-5">
    <div class="container">
        <div id="moderationReportsApp"
             data-csrf-token="<?= htmlspecialchars($csrfToken) ?>">
            <div class="row align-items-end g-3 mb-4">
                <div class="col-sm-6 col-md-4">
                    <label class="form-label" for="moderationStatusFilter">Report status</label>
                    <select class="form-select" id="moderationStatusFilter">
                        <option value="pending" selected>Pending</option>
                        <option value="reviewed">Reviewed</option>
                        <option value="actioned">Actioned</option>
                        <option value="dismissed">Dismissed</option>
                        <option value="all">All reports</option>
                    </select>
                </div>
            </div>

            <p id="moderationStatus"
               class="alert d-none"
               role="status"
               aria-live="polite"></p>
            <div id="moderationReports" class="moderation-reports-list"></div>

            <nav id="moderationPagination"
                 class="d-none mt-4"
                 aria-label="Moderation report pages">
                <div class="d-flex align-items-center justify-content-center gap-3">
                    <button type="button"
                            class="btn btn-outline-pup"
                            id="moderationPrevious"
                            aria-label="Previous page">Previous</button>
                    <span id="moderationPageStatus"
                          class="small text-muted"
                          aria-live="polite"></span>
                    <button type="button"
                            class="btn btn-outline-pup"
                            id="moderationNext"
                            aria-label="Next page">Next</button>
                </div>
            </nav>
        </div>
    </div>
</main>

<?php require __DIR__ . "/../src/includes/footer.php"; ?>
