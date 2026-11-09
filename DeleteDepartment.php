<?php

require_once "AuthCheck.php";

checkRole(["admin"]);

require_once "DataBaseConnetoin.php";

$message = "";

if (isset($_GET['id'])) {

    $id = (int) $_GET['id'];

    $query = "DELETE FROM departments WHERE id = $id";

    if (mysqli_query($conn, $query)) {

        header("Location: ViewDepartments.php");
        exit();

    } else {

        $message = "Error: " . mysqli_error($conn);

    }

} else {

    $message = "Department ID is missing.";

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . "/includes/tailwind.php"; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Department</title>
</head>
<body class="min-h-screen bg-slate-100">
    <div class="flex min-h-screen items-center justify-center px-4 py-10">
        <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm">

            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-xl font-bold text-slate-600">!</div>
            <h1 class="text-xl font-bold tracking-tight text-slate-900">Delete Department</h1>

            <p class="mt-3 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <?php echo htmlspecialchars($message); ?>
            </p>

            <div class="mt-6 flex flex-wrap items-center justify-center gap-2">
                <a href="Dashboard.php"
                    class="inline-block rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Back to Dashboard
                </a>
                <a href="ViewDepartments.php"
                    class="inline-block rounded-lg bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-700">
                    Back to Departments
                </a>
            </div>

        </div>
    </div>
</body>
</html>
