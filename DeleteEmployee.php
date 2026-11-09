<?php
require_once "AuthCheck.php";

checkRole(["admin"]);
require_once "DataBaseConnetoin.php";

$message = "";

if (isset($_GET["id"])) {

    $id = (int) $_GET["id"];

    $sql = "DELETE FROM employees WHERE id = $id";

    if (mysqli_query($conn, $sql)) {

        if (mysqli_affected_rows($conn) > 0) {
            $message = "Employee deleted successfully!";
        } else {
            $message = "Employee not found.";
        }

    } else {
        $message = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . "/includes/tailwind.php"; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Employee</title>
</head>
<body class="min-h-screen bg-slate-100">
    <div class="flex min-h-screen items-center justify-center px-4 py-10">
        <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm">

            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-xl font-bold text-slate-600">!</div>
            <h1 class="text-xl font-bold tracking-tight text-slate-900">Delete Employee</h1>

            <p class="mt-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                <?php echo htmlspecialchars($message); ?>
            </p>

            <div class="mt-6 flex flex-wrap items-center justify-center gap-2">
                <a href="Dashboard.php"
                    class="inline-block rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Back to Dashboard
                </a>
                <a href="ViewEmployees.php"
                    class="inline-block rounded-lg bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-700">
                    Back to Employees
                </a>
            </div>

        </div>
    </div>
</body>
</html>
