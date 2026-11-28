<?php
mysqli_report(MYSQLI_REPORT_OFF);
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../src/Middleware/auth.php";

header("Content-Type: application/json; charset=UTF-8");
ensureSessionStarted();
restoreRememberedSession();

function reportsJsonResponse(int $statusCode, array $data): void
{
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    exit;
}

function reportsBindParams(mysqli_stmt $statement, string $types, array &$params): bool
{
    if ($types === "") {
        return true;
    }

    $arguments = [$types];
    foreach ($params as &$param) {
        $arguments[] = &$param;
    }
    unset($param);

    return call_user_func_array([$statement, "bind_param"], $arguments);
}

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (!isAuthenticated()) {
        mysqli_close($conn);
        reportsJsonResponse(401, ["success" => false, "message" => "Sign in to view moderation reports."]);
    }
    if (!isAdmin()) {
        mysqli_close($conn);
        reportsJsonResponse(403, ["success" => false, "message" => "Administrator access required."]);
    }

    $status = (string) ($_GET["status"] ?? "pending");
    $page = filter_var($_GET["page"] ?? 1, FILTER_VALIDATE_INT);
    $statuses = ["pending", "reviewed", "dismissed", "actioned"];
    if (
        ($status !== "all" && !in_array($status, $statuses, true)) ||
        $page === false ||
        $page < 1
    ) {
        mysqli_close($conn);
        reportsJsonResponse(422, ["success" => false, "message" => "Invalid report filter or page."]);
    }

    $filterSql = $status === "all" ? "" : " WHERE status = ?";
    $countStmt = mysqli_prepare($conn, "SELECT COUNT(*) FROM marketplace_reports" . $filterSql);
    $countParams = $status === "all" ? [] : [$status];
    $countTypes = $status === "all" ? "" : "s";
    if (
        !$countStmt ||
        !reportsBindParams($countStmt, $countTypes, $countParams) ||
        !mysqli_stmt_execute($countStmt)
    ) {
        error_log("Unable to count marketplace moderation reports.");
        if ($countStmt) {
            mysqli_stmt_close($countStmt);
        }
        mysqli_close($conn);
        reportsJsonResponse(500, ["success" => false, "message" => "Unable to load reports."]);
    }

    $total = 0;
    mysqli_stmt_bind_result($countStmt, $total);
    mysqli_stmt_fetch($countStmt);
    mysqli_stmt_close($countStmt);

    $perPage = 20;
    $total = (int) $total;
    $totalPages = max(1, (int) ceil($total / $perPage));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $perPage;
    $query = "SELECT r.report_id, r.target_type, r.listing_id, r.reported_user_id,
                     r.listing_name_snapshot, r.reported_username_snapshot, r.reason,
                     r.details, r.status, r.moderator_note, r.created_at,
                     reporter.username AS reporter_username
              FROM marketplace_reports r
              INNER JOIN users reporter ON reporter.user_id = r.reporter_user_id"
        . ($status === "all" ? "" : " WHERE r.status = ?") .
        " ORDER BY (r.status = 'pending') DESC, r.created_at DESC, r.report_id DESC
          LIMIT ? OFFSET ?";
    $stmt = mysqli_prepare($conn, $query);
    $params = $status === "all" ? [$perPage, $offset] : [$status, $perPage, $offset];
    $types = $status === "all" ? "ii" : "sii";
    if (!$stmt || !reportsBindParams($stmt, $types, $params) || !mysqli_stmt_execute($stmt)) {
        error_log("Unable to load marketplace moderation reports.");
        if ($stmt) {
            mysqli_stmt_close($stmt);
        }
        mysqli_close($conn);
        reportsJsonResponse(500, ["success" => false, "message" => "Unable to load reports."]);
    }

    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        error_log("Unable to read marketplace moderation reports: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        reportsJsonResponse(500, ["success" => false, "message" => "Unable to load reports."]);
    }

    $reports = [];
    while ($report = mysqli_fetch_assoc($result)) {
        $report["report_id"] = (int) $report["report_id"];
        $report["listing_id"] = $report["listing_id"] === null ? null : (int) $report["listing_id"];
        $report["reported_user_id"] = $report["reported_user_id"] === null
            ? null
            : (int) $report["reported_user_id"];
        $reports[] = $report;
    }

    mysqli_free_result($result);
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    reportsJsonResponse(200, [
        "success" => true,
        "reports" => $reports,
        "pagination" => [
            "page" => $page,
            "perPage" => $perPage,
            "total" => $total,
            "totalPages" => $totalPages
        ]
    ]);
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    mysqli_close($conn);
    reportsJsonResponse(405, ["success" => false, "message" => "Method not allowed."]);
}

if (!isAuthenticated()) {
    mysqli_close($conn);
    reportsJsonResponse(401, ["success" => false, "message" => "Sign in to submit or review a report."]);
}
if (!isValidCsrfToken($_SERVER["HTTP_X_CSRF_TOKEN"] ?? null)) {
    mysqli_close($conn);
    reportsJsonResponse(419, ["success" => false, "message" => "Refresh the page and try again."]);
}

$action = (string) ($_POST["action"] ?? "create");
if ($action === "review") {
    if (!isAdmin()) {
        mysqli_close($conn);
        reportsJsonResponse(403, ["success" => false, "message" => "Administrator access required."]);
    }

    $reportId = filter_var($_POST["report_id"] ?? null, FILTER_VALIDATE_INT);
    $status = (string) ($_POST["status"] ?? "");
    $moderatorNote = trim((string) ($_POST["moderator_note"] ?? ""));
    $allowedStatuses = ["pending", "reviewed", "dismissed", "actioned"];
    if (
        $reportId === false ||
        $reportId < 1 ||
        !in_array($status, $allowedStatuses, true) ||
        strlen($moderatorNote) > 8000 ||
        preg_match('//u', $moderatorNote) !== 1
    ) {
        mysqli_close($conn);
        reportsJsonResponse(422, ["success" => false, "message" => "Check the report status and note."]);
    }

    $moderatorId = (int) $_SESSION["user_id"];
    $stmt = mysqli_prepare(
        $conn,
        "UPDATE marketplace_reports
         SET status = ?, moderator_user_id = ?, moderator_note = ?,
             reviewed_at = IF(? = 'pending', NULL, UTC_TIMESTAMP())
         WHERE report_id = ?"
    );
    if (!$stmt) {
        error_log("Unable to prepare moderation report update.");
        mysqli_close($conn);
        reportsJsonResponse(500, ["success" => false, "message" => "Unable to update the report."]);
    }

    mysqli_stmt_bind_param($stmt, "sissi", $status, $moderatorId, $moderatorNote, $status, $reportId);
    if (!mysqli_stmt_execute($stmt)) {
        error_log("Unable to update moderation report: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        reportsJsonResponse(500, ["success" => false, "message" => "Unable to update the report."]);
    }

    $exists = mysqli_stmt_affected_rows($stmt) === 1;
    mysqli_stmt_close($stmt);
    if (!$exists) {
        $checkStmt = mysqli_prepare(
            $conn,
            "SELECT report_id FROM marketplace_reports WHERE report_id = ? LIMIT 1"
        );
        if (!$checkStmt) {
            error_log("Unable to verify moderation report update.");
            mysqli_close($conn);
            reportsJsonResponse(500, ["success" => false, "message" => "Unable to update the report."]);
        }
        mysqli_stmt_bind_param($checkStmt, "i", $reportId);
        if (!mysqli_stmt_execute($checkStmt)) {
            error_log("Unable to verify moderation report update: " . mysqli_stmt_error($checkStmt));
            mysqli_stmt_close($checkStmt);
            mysqli_close($conn);
            reportsJsonResponse(500, ["success" => false, "message" => "Unable to update the report."]);
        }
        mysqli_stmt_store_result($checkStmt);
        $exists = mysqli_stmt_num_rows($checkStmt) === 1;
        mysqli_stmt_close($checkStmt);
    }
    mysqli_close($conn);
    if (!$exists) {
        reportsJsonResponse(404, ["success" => false, "message" => "Report not found."]);
    }

    reportsJsonResponse(200, ["success" => true, "reportId" => (int) $reportId]);
}

if ($action !== "create") {
    mysqli_close($conn);
    reportsJsonResponse(422, ["success" => false, "message" => "Invalid report action."]);
}

$listingId = filter_var($_POST["listing_id"] ?? null, FILTER_VALIDATE_INT);
$targetType = (string) ($_POST["target_type"] ?? "");
$reason = (string) ($_POST["reason"] ?? "");
$details = trim((string) ($_POST["details"] ?? ""));
$allowedTargets = ["listing", "seller"];
$allowedReasons = ["scam", "prohibited_item", "misleading_listing", "harassment", "other"];
if (
    $listingId === false ||
    $listingId < 1 ||
    !in_array($targetType, $allowedTargets, true) ||
    !in_array($reason, $allowedReasons, true) ||
    strlen($details) > 4000 ||
    preg_match('//u', $details) !== 1
) {
    mysqli_close($conn);
    reportsJsonResponse(422, ["success" => false, "message" => "Check the report details and try again."]);
}

$lookupStmt = mysqli_prepare(
    $conn,
    "SELECT l.name, l.seller_user_id, u.username
     FROM marketplace_listings l
     INNER JOIN users u ON u.user_id = l.seller_user_id
     WHERE l.listing_id = ?
     LIMIT 1"
);
if (!$lookupStmt) {
    error_log("Unable to prepare report target lookup.");
    mysqli_close($conn);
    reportsJsonResponse(500, ["success" => false, "message" => "Unable to submit the report."]);
}
mysqli_stmt_bind_param($lookupStmt, "i", $listingId);
if (!mysqli_stmt_execute($lookupStmt)) {
    error_log("Unable to look up report target: " . mysqli_stmt_error($lookupStmt));
    mysqli_stmt_close($lookupStmt);
    mysqli_close($conn);
    reportsJsonResponse(500, ["success" => false, "message" => "Unable to submit the report."]);
}

$listingName = "";
$sellerId = 0;
$sellerUsername = "";
mysqli_stmt_bind_result($lookupStmt, $listingName, $sellerId, $sellerUsername);
$listingFound = mysqli_stmt_fetch($lookupStmt);
mysqli_stmt_close($lookupStmt);
if (!$listingFound) {
    mysqli_close($conn);
    reportsJsonResponse(404, ["success" => false, "message" => "That listing is no longer available."]);
}

$reporterId = (int) $_SESSION["user_id"];
if ((int) $sellerId === $reporterId) {
    mysqli_close($conn);
    reportsJsonResponse(422, ["success" => false, "message" => "You cannot report your own listing or account."]);
}

$duplicateStmt = mysqli_prepare(
    $conn,
    "SELECT report_id
     FROM marketplace_reports
     WHERE reporter_user_id = ? AND listing_id = ? AND target_type = ?
     LIMIT 1"
);
if (!$duplicateStmt) {
    error_log("Unable to prepare duplicate report check.");
    mysqli_close($conn);
    reportsJsonResponse(500, ["success" => false, "message" => "Unable to submit the report."]);
}
mysqli_stmt_bind_param($duplicateStmt, "iis", $reporterId, $listingId, $targetType);
if (!mysqli_stmt_execute($duplicateStmt)) {
    error_log("Unable to check for duplicate report: " . mysqli_stmt_error($duplicateStmt));
    mysqli_stmt_close($duplicateStmt);
    mysqli_close($conn);
    reportsJsonResponse(500, ["success" => false, "message" => "Unable to submit the report."]);
}
mysqli_stmt_store_result($duplicateStmt);
$alreadyReported = mysqli_stmt_num_rows($duplicateStmt) > 0;
mysqli_stmt_close($duplicateStmt);
if ($alreadyReported) {
    mysqli_close($conn);
    reportsJsonResponse(409, ["success" => false, "message" => "You have already reported this listing or seller."]);
}

$insertStmt = mysqli_prepare(
    $conn,
    "INSERT INTO marketplace_reports
        (reporter_user_id, listing_id, reported_user_id, target_type,
         listing_name_snapshot, reported_username_snapshot, reason, details)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);
if (!$insertStmt) {
    error_log("Unable to prepare marketplace report submission.");
    mysqli_close($conn);
    reportsJsonResponse(500, ["success" => false, "message" => "Unable to submit the report."]);
}

$detailsValue = $details === "" ? null : $details;
mysqli_stmt_bind_param(
    $insertStmt,
    "iiisssss",
    $reporterId,
    $listingId,
    $sellerId,
    $targetType,
    $listingName,
    $sellerUsername,
    $reason,
    $detailsValue
);
if (!mysqli_stmt_execute($insertStmt)) {
    $duplicate = mysqli_stmt_errno($insertStmt) === 1062;
    if (!$duplicate) {
        error_log("Unable to save marketplace report: " . mysqli_stmt_error($insertStmt));
    }
    mysqli_stmt_close($insertStmt);
    mysqli_close($conn);
    if ($duplicate) {
        reportsJsonResponse(409, ["success" => false, "message" => "You have already reported this listing or seller."]);
    }
    reportsJsonResponse(500, ["success" => false, "message" => "Unable to submit the report."]);
}

$reportId = mysqli_insert_id($conn);
mysqli_stmt_close($insertStmt);
mysqli_close($conn);
reportsJsonResponse(201, ["success" => true, "reportId" => (int) $reportId]);
