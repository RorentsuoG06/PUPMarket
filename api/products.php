<?php
mysqli_report(MYSQLI_REPORT_OFF);
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../src/Middleware/auth.php";
require_once __DIR__ . "/../src/Services/NexconnService.php";

header("Content-Type: application/json; charset=UTF-8");

function productsJsonResponse(int $statusCode, array $data): void
{
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    exit;
}

function productsBindQueryParams(mysqli_stmt $statement, string $types, array &$params): bool
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

function removeUploadedProductImage(string $imagePath): void
{
    if (!preg_match(
        '#^uploads/products/([a-f0-9]{32}\.(?:jpg|png|webp))$#',
        str_replace("\\", "/", $imagePath),
        $matches
    )) {
        return;
    }

    $filePath = __DIR__ . "/../uploads/products/" . $matches[1];
    if (is_file($filePath) && !unlink($filePath)) {
        error_log("Unable to remove an unused marketplace product image.");
    }
}

ensureSessionStarted();
restoreRememberedSession();

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $mineParam = (string) ($_GET["mine"] ?? "0");
    if (!in_array($mineParam, ["0", "1"], true)) {
        mysqli_close($conn);
        productsJsonResponse(422, ["success" => false, "message" => "Invalid listing filter."]);
    }

    $viewerId = isset($_SESSION["user_id"]) ? (int) $_SESSION["user_id"] : 0;
    $mineOnly = $mineParam === "1";
    if ($mineOnly && !isAuthenticated()) {
        mysqli_close($conn);
        productsJsonResponse(401, ["success" => false, "message" => "Sign in to view your listings."]);
    }

    $search = trim((string) ($_GET["q"] ?? ""));
    $category = trim((string) ($_GET["category"] ?? "all"));
    $sort = (string) ($_GET["sort"] ?? "default");
    $page = filter_var($_GET["page"] ?? 1, FILTER_VALIDATE_INT);
    $perPage = filter_var($_GET["per_page"] ?? 12, FILTER_VALIDATE_INT);
    $categories = ["Books", "Uniforms", "Electronics", "School Supplies", "Others"];
    $sortOptions = [
        "default" => "l.created_at DESC, l.listing_id DESC",
        "low" => "l.price ASC, l.listing_id DESC",
        "high" => "l.price DESC, l.listing_id DESC",
        "name" => "l.name ASC, l.listing_id DESC"
    ];

    if (
        strlen($search) > 480 ||
        preg_match('//u', $search) !== 1 ||
        ($category !== "" && $category !== "all" && !in_array($category, $categories, true)) ||
        !isset($sortOptions[$sort]) ||
        $page === false ||
        $page < 1 ||
        $perPage === false ||
        $perPage < 1 ||
        $perPage > 48
    ) {
        mysqli_close($conn);
        productsJsonResponse(422, ["success" => false, "message" => "Invalid listing search or page."]);
    }

    $filters = [];
    $filterParams = [];
    $filterTypes = "";
    if ($mineOnly) {
        $filters[] = "l.seller_user_id = ?";
        $filterParams[] = $viewerId;
        $filterTypes .= "i";
    }
    if ($category !== "" && $category !== "all") {
        $filters[] = "l.category = ?";
        $filterParams[] = $category;
        $filterTypes .= "s";
    }
    if ($search !== "") {
        $filters[] = "(l.name LIKE ? OR l.description LIKE ? OR u.full_name LIKE ?)";
        $like = "%" . $search . "%";
        array_push($filterParams, $like, $like, $like);
        $filterTypes .= "sss";
    }

    $whereSql = $filters === [] ? "" : " WHERE " . implode(" AND ", $filters);
    $fromSql = " FROM marketplace_listings l
                 INNER JOIN users u ON u.user_id = l.seller_user_id";
    $countStmt = mysqli_prepare($conn, "SELECT COUNT(*)" . $fromSql . $whereSql);
    if (!$countStmt || !productsBindQueryParams($countStmt, $filterTypes, $filterParams)) {
        error_log("Unable to prepare marketplace listing count.");
        if ($countStmt) {
            mysqli_stmt_close($countStmt);
        }
        mysqli_close($conn);
        productsJsonResponse(500, ["success" => false, "message" => "Unable to load listings."]);
    }
    if (!mysqli_stmt_execute($countStmt)) {
        error_log("Unable to count marketplace listings: " . mysqli_stmt_error($countStmt));
        mysqli_stmt_close($countStmt);
        mysqli_close($conn);
        productsJsonResponse(500, ["success" => false, "message" => "Unable to load listings."]);
    }

    $total = 0;
    mysqli_stmt_bind_result($countStmt, $total);
    mysqli_stmt_fetch($countStmt);
    mysqli_stmt_close($countStmt);

    $total = (int) $total;
    $totalPages = max(1, (int) ceil($total / $perPage));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $perPage;
    $queryParams = array_merge($filterParams, [$perPage, $offset]);
    $queryTypes = $filterTypes . "ii";
    $query = "SELECT l.listing_id, l.seller_user_id, l.name, l.price, l.category,
                     l.item_condition AS `condition`, l.description,
                     l.image_path AS image, u.full_name AS seller,
                     u.nexconn_user_id AS sellerUserId"
        . $fromSql . $whereSql .
        " ORDER BY " . $sortOptions[$sort] . " LIMIT ? OFFSET ?";
    $listStmt = mysqli_prepare($conn, $query);
    if (!$listStmt || !productsBindQueryParams($listStmt, $queryTypes, $queryParams)) {
        error_log("Unable to prepare marketplace listing page.");
        if ($listStmt) {
            mysqli_stmt_close($listStmt);
        }
        mysqli_close($conn);
        productsJsonResponse(500, ["success" => false, "message" => "Unable to load listings."]);
    }
    if (!mysqli_stmt_execute($listStmt)) {
        error_log("Unable to load marketplace listing page: " . mysqli_stmt_error($listStmt));
        mysqli_stmt_close($listStmt);
        mysqli_close($conn);
        productsJsonResponse(500, ["success" => false, "message" => "Unable to load listings."]);
    }

    $result = mysqli_stmt_get_result($listStmt);
    if (!$result) {
        error_log("Unable to read marketplace listing page: " . mysqli_stmt_error($listStmt));
        mysqli_stmt_close($listStmt);
        mysqli_close($conn);
        productsJsonResponse(500, ["success" => false, "message" => "Unable to load listings."]);
    }

    $listings = [];
    while ($listing = mysqli_fetch_assoc($result)) {
        $listing["id"] = "market-" . (int) $listing["listing_id"];
        $listing["listingId"] = (int) $listing["listing_id"];
        $listing["price"] = (float) $listing["price"];
        $listing["isOwnListing"] = $viewerId > 0 &&
            (int) $listing["seller_user_id"] === $viewerId;
        $listing["canContactSeller"] = $viewerId > 0 &&
            !$listing["isOwnListing"] &&
            is_string($listing["sellerUserId"]) &&
            preg_match('/^[a-f0-9]{32}$/', $listing["sellerUserId"]) === 1;
        unset($listing["listing_id"], $listing["seller_user_id"]);
        $listing["isServerListing"] = true;
        $listings[] = $listing;
    }

    mysqli_free_result($result);
    mysqli_stmt_close($listStmt);
    mysqli_close($conn);
    productsJsonResponse(200, [
        "success" => true,
        "products" => $listings,
        "pagination" => [
            "page" => $page,
            "perPage" => (int) $perPage,
            "total" => $total,
            "totalPages" => $totalPages
        ]
    ]);
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    mysqli_close($conn);
    productsJsonResponse(405, ["success" => false, "message" => "Method not allowed."]);
}

if (!isAuthenticated()) {
    mysqli_close($conn);
    productsJsonResponse(401, ["success" => false, "message" => "Sign in to post a listing."]);
}

if (!isValidCsrfToken($_SERVER["HTTP_X_CSRF_TOKEN"] ?? null)) {
    mysqli_close($conn);
    productsJsonResponse(419, ["success" => false, "message" => "Refresh the page and try again."]);
}

$action = (string) ($_POST["action"] ?? "create");
if (!in_array($action, ["create", "update", "delete"], true)) {
    mysqli_close($conn);
    productsJsonResponse(422, ["success" => false, "message" => "Invalid listing action."]);
}

if ($action === "delete") {
    $listingId = filter_var($_POST["listing_id"] ?? null, FILTER_VALIDATE_INT);
    if ($listingId === false || $listingId < 1) {
        mysqli_close($conn);
        productsJsonResponse(422, ["success" => false, "message" => "Invalid listing."]);
    }

    $sellerUserId = (int) $_SESSION["user_id"];
    $imageStmt = mysqli_prepare(
        $conn,
        "SELECT image_path
         FROM marketplace_listings
         WHERE listing_id = ? AND seller_user_id = ?
         LIMIT 1"
    );
    if (!$imageStmt) {
        error_log("Unable to prepare marketplace listing removal lookup.");
        mysqli_close($conn);
        productsJsonResponse(500, ["success" => false, "message" => "Unable to remove the listing."]);
    }
    mysqli_stmt_bind_param($imageStmt, "ii", $listingId, $sellerUserId);
    if (!mysqli_stmt_execute($imageStmt)) {
        error_log("Unable to look up marketplace listing for removal: " . mysqli_stmt_error($imageStmt));
        mysqli_stmt_close($imageStmt);
        mysqli_close($conn);
        productsJsonResponse(500, ["success" => false, "message" => "Unable to remove the listing."]);
    }

    $imagePath = "";
    mysqli_stmt_bind_result($imageStmt, $imagePath);
    $listingFound = mysqli_stmt_fetch($imageStmt);
    mysqli_stmt_close($imageStmt);
    if (!$listingFound) {
        mysqli_close($conn);
        productsJsonResponse(404, ["success" => false, "message" => "Listing not found."]);
    }
    $imagePath = (string) $imagePath;

    $deleteStmt = mysqli_prepare(
        $conn,
        "DELETE FROM marketplace_listings
         WHERE listing_id = ? AND seller_user_id = ?"
    );
    if (!$deleteStmt) {
        error_log("Unable to prepare marketplace listing removal.");
        mysqli_close($conn);
        productsJsonResponse(500, ["success" => false, "message" => "Unable to remove the listing."]);
    }
    mysqli_stmt_bind_param($deleteStmt, "ii", $listingId, $sellerUserId);
    if (!mysqli_stmt_execute($deleteStmt)) {
        error_log("Unable to remove marketplace listing: " . mysqli_stmt_error($deleteStmt));
        mysqli_stmt_close($deleteStmt);
        mysqli_close($conn);
        productsJsonResponse(500, ["success" => false, "message" => "Unable to remove the listing."]);
    }
    if (mysqli_stmt_affected_rows($deleteStmt) !== 1) {
        mysqli_stmt_close($deleteStmt);
        mysqli_close($conn);
        productsJsonResponse(404, ["success" => false, "message" => "Listing not found."]);
    }

    mysqli_stmt_close($deleteStmt);
    mysqli_close($conn);
    removeUploadedProductImage($imagePath);
    productsJsonResponse(200, ["success" => true, "listingId" => (int) $listingId]);
}

$name = trim((string) ($_POST["name"] ?? ""));
$category = trim((string) ($_POST["category"] ?? ""));
$condition = trim((string) ($_POST["condition"] ?? ""));
$description = trim((string) ($_POST["description"] ?? ""));
$priceInput = trim((string) ($_POST["price"] ?? ""));
$validPriceFormat = preg_match('/^\d+(?:\.\d{1,2})?$/D', $priceInput) === 1;
$allowedCategories = ["Books", "Uniforms", "Electronics", "School Supplies", "Others"];
$allowedConditions = ["New", "Like New", "Good", "Fair"];

if (
    $name === "" ||
    strlen($name) > 120 ||
    preg_match('//u', $name) !== 1 ||
    !in_array($category, $allowedCategories, true) ||
    !in_array($condition, $allowedConditions, true) ||
    $description === "" ||
    strlen($description) > 5000 ||
    preg_match('//u', $description) !== 1 ||
    !$validPriceFormat ||
    !is_numeric($priceInput) ||
    (float) $priceInput <= 0 ||
    (float) $priceInput > 99999999.99
) {
    mysqli_close($conn);
    productsJsonResponse(422, ["success" => false, "message" => "Check the listing details and try again."]);
}

$imageExtensions = [
    "image/jpeg" => "jpg",
    "image/png" => "png",
    "image/webp" => "webp"
];

$listingId = null;
$currentImagePath = "";
if ($action === "update") {
    $listingId = filter_var($_POST["listing_id"] ?? null, FILTER_VALIDATE_INT);
    if ($listingId === false || $listingId < 1) {
        mysqli_close($conn);
        productsJsonResponse(422, ["success" => false, "message" => "Invalid listing."]);
    }

    $sellerUserId = (int) $_SESSION["user_id"];
    $existingStmt = mysqli_prepare(
        $conn,
        "SELECT image_path
         FROM marketplace_listings
         WHERE listing_id = ? AND seller_user_id = ?
         LIMIT 1"
    );
    if (!$existingStmt) {
        error_log("Unable to prepare marketplace listing update lookup.");
        mysqli_close($conn);
        productsJsonResponse(500, ["success" => false, "message" => "Unable to update the listing."]);
    }
    mysqli_stmt_bind_param($existingStmt, "ii", $listingId, $sellerUserId);
    if (!mysqli_stmt_execute($existingStmt)) {
        error_log("Unable to look up marketplace listing for update: " . mysqli_stmt_error($existingStmt));
        mysqli_stmt_close($existingStmt);
        mysqli_close($conn);
        productsJsonResponse(500, ["success" => false, "message" => "Unable to update the listing."]);
    }
    mysqli_stmt_bind_result($existingStmt, $currentImagePath);
    $listingFound = mysqli_stmt_fetch($existingStmt);
    mysqli_stmt_close($existingStmt);
    if (!$listingFound) {
        mysqli_close($conn);
        productsJsonResponse(404, ["success" => false, "message" => "Listing not found."]);
    }
    $currentImagePath = (string) $currentImagePath;
}

$sellerUserId = (int) $_SESSION["user_id"];
$sellerNexconnId = $action === "create" ? nexconnUserId($conn, $sellerUserId) : null;
if ($action === "create" && $sellerNexconnId === null) {
    mysqli_close($conn);
    productsJsonResponse(500, ["success" => false, "message" => "Unable to link this listing to your account."]);
}

$image = $_FILES["image"] ?? null;
$hasImage = is_array($image) && ($image["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
if (
    ($action === "create" && !$hasImage) ||
    ($hasImage && (
        ($image["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK ||
        !is_uploaded_file($image["tmp_name"] ?? "") ||
        ($image["size"] ?? 0) < 1 ||
        $image["size"] > 2 * 1024 * 1024
    ))
) {
    mysqli_close($conn);
    productsJsonResponse(422, ["success" => false, "message" => "Choose an image smaller than 2MB."]);
}

$imageUrl = $currentImagePath;
$uploadedImagePath = "";
if ($hasImage) {
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($image["tmp_name"]);
    if (!isset($imageExtensions[$mimeType])) {
        mysqli_close($conn);
        productsJsonResponse(422, ["success" => false, "message" => "Use a JPG, PNG, or WEBP image."]);
    }

    $uploadDirectory = __DIR__ . "/../uploads/products";
    if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
        mysqli_close($conn);
        error_log("Unable to create the marketplace product upload directory.");
        productsJsonResponse(500, ["success" => false, "message" => "Unable to save the product image."]);
    }

    $imageFileName = bin2hex(random_bytes(16)) . "." . $imageExtensions[$mimeType];
    $uploadedImagePath = $uploadDirectory . "/" . $imageFileName;
    $imageUrl = "uploads/products/" . $imageFileName;
    if (!move_uploaded_file($image["tmp_name"], $uploadedImagePath)) {
        mysqli_close($conn);
        error_log("Unable to move an uploaded marketplace product image.");
        productsJsonResponse(500, ["success" => false, "message" => "Unable to save the product image."]);
    }
}

$price = round((float) $priceInput, 2);
if ($action === "update") {
    $stmt = mysqli_prepare(
        $conn,
        "UPDATE marketplace_listings
         SET name = ?, price = ?, category = ?, item_condition = ?, description = ?, image_path = ?
         WHERE listing_id = ? AND seller_user_id = ?"
    );
} else {
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO marketplace_listings
            (seller_user_id, name, price, category, item_condition, description, image_path)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
}
if (!$stmt) {
    if ($uploadedImagePath !== "") {
        unlink($uploadedImagePath);
    }
    mysqli_close($conn);
    error_log("Unable to prepare marketplace listing save.");
    productsJsonResponse(500, ["success" => false, "message" => "Unable to save the listing."]);
}

if ($action === "update") {
    mysqli_stmt_bind_param(
        $stmt,
        "sdssssii",
        $name,
        $price,
        $category,
        $condition,
        $description,
        $imageUrl,
        $listingId,
        $sellerUserId
    );
} else {
    mysqli_stmt_bind_param(
        $stmt,
        "isdssss",
        $sellerUserId,
        $name,
        $price,
        $category,
        $condition,
        $description,
        $imageUrl
    );
}
if (!mysqli_stmt_execute($stmt)) {
    error_log("Unable to save marketplace listing: " . mysqli_stmt_error($stmt));
    mysqli_stmt_close($stmt);
    if ($uploadedImagePath !== "") {
        unlink($uploadedImagePath);
    }
    mysqli_close($conn);
    productsJsonResponse(500, ["success" => false, "message" => "Unable to save the listing."]);
}

$affectedRows = mysqli_stmt_affected_rows($stmt);
if ($action === "update" && $affectedRows !== 1 && $affectedRows !== 0) {
    mysqli_stmt_close($stmt);
    if ($uploadedImagePath !== "") {
        unlink($uploadedImagePath);
    }
    mysqli_close($conn);
    productsJsonResponse(500, ["success" => false, "message" => "Unable to update the listing."]);
}
if ($action === "update" && $affectedRows === 0) {
    $verifyStmt = mysqli_prepare(
        $conn,
        "SELECT 1
         FROM marketplace_listings
         WHERE listing_id = ? AND seller_user_id = ?
         LIMIT 1"
    );
    if (!$verifyStmt) {
        mysqli_stmt_close($stmt);
        if ($uploadedImagePath !== "") {
            unlink($uploadedImagePath);
        }
        error_log("Unable to verify marketplace listing update.");
        mysqli_close($conn);
        productsJsonResponse(500, ["success" => false, "message" => "Unable to update the listing."]);
    }

    mysqli_stmt_bind_param($verifyStmt, "ii", $listingId, $sellerUserId);
    if (!mysqli_stmt_execute($verifyStmt)) {
        error_log("Unable to verify marketplace listing update: " . mysqli_stmt_error($verifyStmt));
        mysqli_stmt_close($verifyStmt);
        mysqli_stmt_close($stmt);
        if ($uploadedImagePath !== "") {
            unlink($uploadedImagePath);
        }
        mysqli_close($conn);
        productsJsonResponse(500, ["success" => false, "message" => "Unable to update the listing."]);
    }
    mysqli_stmt_store_result($verifyStmt);
    $listingStillExists = mysqli_stmt_num_rows($verifyStmt) === 1;
    mysqli_stmt_close($verifyStmt);
    if (!$listingStillExists) {
        mysqli_stmt_close($stmt);
        if ($uploadedImagePath !== "") {
            unlink($uploadedImagePath);
        }
        mysqli_close($conn);
        productsJsonResponse(404, ["success" => false, "message" => "Listing not found."]);
    }
}

$previousImagePath = $currentImagePath;
if ($action === "create") {
    $listingId = mysqli_insert_id($conn);
}
mysqli_stmt_close($stmt);
mysqli_close($conn);

if ($action === "update" && $uploadedImagePath !== "") {
    removeUploadedProductImage($previousImagePath);
    productsJsonResponse(200, [
        "success" => true,
        "listingId" => (int) $listingId
    ]);
}

if ($action === "update") {
    productsJsonResponse(200, [
        "success" => true,
        "listingId" => (int) $listingId
    ]);
}

productsJsonResponse(201, [
    "success" => true,
    "product" => [
        "id" => "market-" . $listingId,
        "listingId" => $listingId,
        "name" => $name,
        "price" => $price,
        "category" => $category,
        "condition" => $condition,
        "description" => $description,
        "image" => $imageUrl,
        "seller" => $_SESSION["full_name"] ?? "PUPMarket Student",
        "sellerUserId" => $sellerNexconnId,
        "isOwnListing" => true,
        "canContactSeller" => false,
        "isServerListing" => true
    ]
]);
