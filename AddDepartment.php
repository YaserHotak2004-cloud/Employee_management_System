<?php
require_once "AuthCheck.php";

checkRole(["admin"]);

require_once "DataBaseConnetoin.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = mysqli_real_escape_string(
        $conn,
        trim($_POST["name"] ?? "")
    );

    $description = mysqli_real_escape_string(
        $conn,
        trim($_POST["description"] ?? "")
    );

    if ($name == "") {

        $message = "Department name is required.";

    } else {

        $sql = "INSERT INTO departments (name, description)
                VALUES ('$name', '$description')";

        if (mysqli_query($conn, $sql)) {

            $message = "Department added successfully!";

        } else {

            $message = "Error: " . mysqli_error($conn);

        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include __DIR__ . "/includes/tailwind.php"; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Department - Employee Management System</title>
</head>

<body class="min-h-screen bg-slate-100">
    <div class="mx-auto max-w-2xl px-4 py-10">

        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-cyan-700">Departments</p>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Add New Department</h1>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="Dashboard.php"
                    class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Back to Dashboard
                </a>
                <a href="ViewDepartments.php"
                    class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Back to Departments
                </a>
            </div>
        </div>

        <?php if ($message !== "") { ?>
            <div class="mb-5 rounded-lg border border-cyan-200 bg-cyan-50 px-4 py-3 text-sm font-medium text-cyan-800">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php } ?>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <form method="POST" action="AddDepartment.php" class="space-y-5">

                <div>
                    <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Department Name</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200"
                    >
                </div>

                <div>
                    <label for="description" class="mb-1 block text-sm font-medium text-slate-700">Description</label>
                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200"
                    ></textarea>
                </div>

                <button type="submit"
                    class="w-full rounded-lg bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-700 sm:w-auto">
                    Add Department
                </button>

            </form>
        </div>

    </div>
</body>
</html>
