<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../DataBaseConnetoin.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../Login.php");
    exit;
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, name, email, role, status FROM users WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $_SESSION["user_id"]);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$currentUser = $result ? mysqli_fetch_assoc($result) : null;

mysqli_stmt_close($stmt);

if (!$currentUser || ($currentUser["status"] ?? "Active") === "Inactive") {
    $_SESSION = [];
    session_destroy();

    header("Location: ../Login.php");
    exit;
}

// Keep the session in sync with the database, so role changes apply immediately.
$_SESSION["user_name"] = $currentUser["name"];
$_SESSION["user_email"] = $currentUser["email"];
$_SESSION["user_role"] = $currentUser["role"];
