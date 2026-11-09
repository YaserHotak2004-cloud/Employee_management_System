<?php

require_once "AuthCheck.php";

checkRole(["admin"]);

require_once "DataBaseConnetoin.php";

$query = "SELECT * FROM departments";
$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . "/includes/tailwind.php"; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Departments</title>
</head>
<body class="min-h-screen bg-slate-100">
    <div class="mx-auto max-w-5xl px-4 py-10">

        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-cyan-700">Organization</p>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">All Departments</h1>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="Dashboard.php"
                    class="rounded-lg border border-slate-300 bg-gray-800 hover:text-black px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-50">
                    Back to Dashboard
                </a>
                <a href="AddDepartment.php"
                    class="rounded-lg bg-cyan-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-cyan-700">
                    Add New Department
                </a>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-4 py-3 font-semibold">ID</th>
                            <th class="px-4 py-3 font-semibold">Department Name</th>
                            <th class="px-4 py-3 font-semibold">Description</th>
                            <th class="px-4 py-3 font-semibold">Created At</th>
                            <th class="px-4 py-3 font-semibold">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">

                        <?php

                        while ($row = mysqli_fetch_assoc($result)) {

                        ?>

                        <tr class="hover:bg-slate-50">

                            <td class="px-4 py-3 text-slate-500">
                                <?php echo $row['id']; ?>
                            </td>

                            <td class="px-4 py-3 font-medium text-slate-900">
                                <?php echo htmlspecialchars($row['name']); ?>
                            </td>

                            <td class="px-4 py-3 text-slate-600">
                                <?php echo htmlspecialchars($row['description']); ?>
                            </td>

                            <td class="px-4 py-3 text-slate-500">
                                <?php echo $row['created_at']; ?>
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3 text-xs font-semibold">
                                    <a href="EditDepartment.php?id=<?php echo $row['id']; ?>"
                                        class="text-cyan-700 hover:underline">
                                        Edit
                                    </a>

                                    <a
                                        href="DeleteDepartment.php?id=<?php echo $row['id']; ?>"
                                        onclick="return confirm('Are you sure you want to delete this department?')"
                                        class="text-rose-600 hover:underline"
                                    >
                                        Delete
                                    </a>
                                </div>
                            </td>

                        </tr>

                        <?php
                        }
                        ?>

                    </tbody>
                </table>
            </div>
        </div>

    </div>
</body>
</html>
