<?php

function nexconnUserId(mysqli $connection, int $userId): ?string
{
    $selectStmt = mysqli_prepare(
        $connection,
        "SELECT nexconn_user_id FROM users WHERE user_id = ? LIMIT 1"
    );
    if (!$selectStmt) {
        error_log("Unable to prepare Nexconn identity lookup.");
        return null;
    }

    mysqli_stmt_bind_param($selectStmt, "i", $userId);
    if (!mysqli_stmt_execute($selectStmt)) {
        error_log("Unable to look up Nexconn identity: " . mysqli_stmt_error($selectStmt));
        mysqli_stmt_close($selectStmt);
        return null;
    }

    $nexconnUserId = null;
    mysqli_stmt_bind_result($selectStmt, $nexconnUserId);
    $found = mysqli_stmt_fetch($selectStmt);
    mysqli_stmt_close($selectStmt);

    if (!$found) {
        return null;
    }
    if (is_string($nexconnUserId) && preg_match('/^[a-f0-9]{32}$/', $nexconnUserId)) {
        return $nexconnUserId;
    }
    if ($nexconnUserId !== null && $nexconnUserId !== "") {
        error_log("Stored Nexconn identity has an invalid format.");
        return null;
    }

    $updateStmt = mysqli_prepare(
        $connection,
        "UPDATE users
         SET nexconn_user_id = ?
         WHERE user_id = ? AND (nexconn_user_id IS NULL OR nexconn_user_id = '')"
    );
    if (!$updateStmt) {
        error_log("Unable to prepare Nexconn identity creation.");
        return null;
    }

    for ($attempt = 0; $attempt < 3; $attempt++) {
        $candidate = bin2hex(random_bytes(16));
        mysqli_stmt_bind_param($updateStmt, "si", $candidate, $userId);
        if (!mysqli_stmt_execute($updateStmt)) {
            error_log("Unable to save Nexconn identity: " . mysqli_stmt_error($updateStmt));
            mysqli_stmt_close($updateStmt);
            return null;
        }

        if (mysqli_stmt_affected_rows($updateStmt) > 0) {
            mysqli_stmt_close($updateStmt);
            return $candidate;
        }

        mysqli_stmt_close($updateStmt);
        return nexconnUserId($connection, $userId);
    }

    mysqli_stmt_close($updateStmt);
    error_log("Unable to allocate a unique Nexconn identity.");
    return null;
}

function nexconnDisplayName(string $name): string
{
    $characters = preg_split('//u', trim($name), -1, PREG_SPLIT_NO_EMPTY);
    if (!is_array($characters) || $characters === []) {
        return "PUPMarket Student";
    }

    return implode("", array_slice($characters, 0, 64));
}
