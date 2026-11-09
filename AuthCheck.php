<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/DataBaseConnetoin.php";

/**
 * Returns the current logged-in user, refreshed from the database.
 * Returns null when the session is invalid or the account is deactivated.
 */
function currentUser()
{
    global $conn;

    static $user = null;
    static $loaded = false;

    if ($loaded) {
        return $user;
    }

    $loaded = true;

    if (!isset($_SESSION["user_id"])) {
        return null;
    }

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, name, email, role, status FROM users WHERE id = ?"
    );

    if (!$stmt) {
        return null;
    }

    mysqli_stmt_bind_param($stmt, "i", $_SESSION["user_id"]);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $user = $result ? (mysqli_fetch_assoc($result) ?: null) : null;

    mysqli_stmt_close($stmt);

    if ($user !== null && ($user["status"] ?? "Active") === "Inactive") {
        $user = null;
    }

    return $user;
}

function checkLogin()
{
    $user = currentUser();

    if ($user === null) {
        $_SESSION = [];
        session_destroy();

        header("Location: Login.php");
        exit();
    }

    // Keep the session in sync with the database, so role changes apply immediately.
    $_SESSION["user_name"] = $user["name"];
    $_SESSION["user_email"] = $user["email"];
    $_SESSION["user_role"] = $user["role"];
}

function checkRole($allowedRoles)
{
    checkLogin();

    $role = $_SESSION["user_role"] ?? "";

    // Super Admin has full access to every protected page.
    if ($role === "super_admin") {
        return;
    }

    if (!in_array($role, $allowedRoles, true)) {
        http_response_code(403);
        die("Access Denied! You are not allowed to access this page.");
    }
}
