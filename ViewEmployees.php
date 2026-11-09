<?php
require_once "AuthCheck.php";

checkRole(["admin", "hr_manager"]);
require_once "DataBaseConnetoin.php";




$search = trim($_GET['search'] ?? '');

$department_id = (int) ($_GET['department_id'] ?? 0);

$status = $_GET['status'] ?? '';

$departmentQuery = "SELECT id, name FROM departments ORDER BY name";

$departmentResult = mysqli_query($conn, $departmentQuery);

if (!$departmentResult) {
    die("Department Query Failed: " . mysqli_error($conn));
}

$query = "SELECT
            employees.*,
            departments.name AS department_name
          FROM employees
          INNER JOIN departments
          ON employees.department_id = departments.id

          WHERE
          (? = ''
            OR employees.first_name LIKE ?
            OR employees.last_name LIKE ?
            OR employees.employee_code LIKE ?)

          AND (? = 0 OR employees.department_id = ?)

          AND (? = '' OR employees.status = ?)

          ORDER BY employees.id DESC";


$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    die("Prepare Failed: " . mysqli_error($conn));
}


$searchPattern = "%" . $search . "%";


mysqli_stmt_bind_param(
    $stmt,
    "ssssiiss",
    $search,
    $searchPattern,
    $searchPattern,
    $searchPattern,
    $department_id,
    $department_id,
    $status,
    $status
);


mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    die("Query Failed: " . mysqli_stmt_error($stmt));
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include __DIR__ . "/includes/tailwind.php"; ?>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>View Employees</title>

</head>

<body class="min-h-screen bg-slate-100">
    <div class="mx-auto max-w-7xl px-4 py-10">

        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-cyan-700">Employees</p>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">All Employees</h1>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="Dashboard.php"
                    class="rounded-lg border border-slate-300 bg-gray-800 text-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 hover:text-black">
                    Back to Dashboard
                </a>
                <a href="AddEmployee.php"
                    class="rounded-lg bg-cyan-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-cyan-700">
                    Add New Employee
                </a>
            </div>
        </div>

        <!-- Search / Filter -->
        <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <form method="GET" action="ViewEmployees.php"
                class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">

                <div class="lg:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Search Employee</label>
                    <input
                        type="text"
                        name="search"
                        placeholder="Name or Employee Code"
                        value="<?php echo htmlspecialchars($search); ?>"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200"
                    >
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Department</label>
                    <select name="department_id"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                        <option value="0">All Departments</option>
                        <?php while ($department = mysqli_fetch_assoc($departmentResult)) { ?>
                            <option
                                value="<?php echo (int) $department['id']; ?>"
                                <?php
                                if ($department_id == $department['id']) {
                                    echo "selected";
                                }
                                ?>
                            >
                                <?php echo htmlspecialchars($department['name']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Status</label>
                    <select name="status"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                        <option value="">All Status</option>
                        <option value="Active" <?php if ($status == "Active") echo "selected"; ?>>Active</option>
                        <option value="Inactive" <?php if ($status == "Inactive") echo "selected"; ?>>Inactive</option>
                    </select>
                </div>

                <div class="flex items-end gap-3 md:col-span-2 lg:col-span-4">
                    <button type="submit"
                        class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                        Search / Filter
                    </button>
                    <a href="ViewEmployees.php"
                        class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        Reset
                    </a>
                </div>

            </form>
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-4 py-3 font-semibold">ID</th>
                            <th class="px-4 py-3 font-semibold">Code</th>
                            <th class="px-4 py-3 font-semibold">First Name</th>
                            <th class="px-4 py-3 font-semibold">Last Name</th>
                            <th class="px-4 py-3 font-semibold">Email</th>
                            <th class="px-4 py-3 font-semibold">Phone</th>
                            <th class="px-4 py-3 font-semibold">Gender</th>
                            <th class="px-4 py-3 font-semibold">Department</th>
                            <th class="px-4 py-3 font-semibold">Position</th>
                            <th class="px-4 py-3 font-semibold">Salary</th>
                            <th class="px-4 py-3 font-semibold">Hire Date</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                            <th class="px-4 py-3 font-semibold">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        <?php if (mysqli_num_rows($result) > 0) { ?>

                            <?php while ($row = mysqli_fetch_assoc($result)) { ?>

                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3 text-slate-500"><?php echo (int) $row['id']; ?></td>
                                    <td class="px-4 py-3 font-medium text-slate-900"><?php echo htmlspecialchars($row['employee_code']); ?></td>
                                    <td class="px-4 py-3"><?php echo htmlspecialchars($row['first_name']); ?></td>
                                    <td class="px-4 py-3"><?php echo htmlspecialchars($row['last_name']); ?></td>
                                    <td class="px-4 py-3"><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td class="px-4 py-3"><?php echo htmlspecialchars($row['phone']); ?></td>
                                    <td class="px-4 py-3"><?php echo htmlspecialchars($row['gender']); ?></td>
                                    <td class="px-4 py-3"><?php echo htmlspecialchars($row['department_name']); ?></td>
                                    <td class="px-4 py-3"><?php echo htmlspecialchars($row['position']); ?></td>
                                    <td class="px-4 py-3 font-medium"><?php echo htmlspecialchars($row['salary']); ?></td>
                                    <td class="px-4 py-3 text-slate-500"><?php echo htmlspecialchars($row['hire_date']); ?></td>
                                    <td class="px-4 py-3">
                                        <?php if ($row['status'] === 'Active') { ?>
                                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Active</span>
                                        <?php } else { ?>
                                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600"><?php echo htmlspecialchars($row['status']); ?></span>
                                        <?php } ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3 text-xs font-semibold">
                                            <a href="EditEmployee.php?id=<?php echo (int) $row['id']; ?>"
                                                class="text-cyan-700 hover:underline">Edit</a>
                                            <?php if (in_array($_SESSION["user_role"], ["admin", "super_admin"], true)) { ?>
                                                <a href="DeleteEmployee.php?id=<?php echo (int) $row['id']; ?>"
                                                    onclick="return confirm('Are you sure you want to delete this employee?')"
                                                    class="text-rose-600 hover:underline">Delete</a>
                                            <?php } ?>
                                        </div>
                                    </td>
                                </tr>

                            <?php } ?>

                        <?php } else { ?>

                            <tr>
                                <td colspan="13" class="px-4 py-10 text-center text-slate-500">No Employees Found.</td>
                            </tr>

                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</body>

</html>

<?php
mysqli_stmt_close($stmt);
?>
