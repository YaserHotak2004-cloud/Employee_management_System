<?php
require_once "AuthCheck.php";
require_once "DataBaseConnetoin.php";

checkRole(["employee"]);

$user_id = $_SESSION["user_id"];

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, name, email, role, status, employee_id, created_at
     FROM users WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$account = $result ? mysqli_fetch_assoc($result) : null;

mysqli_stmt_close($stmt);

if (!$account) {
    die("Account not found. Please login again.");
}

$employee = null;

if (!empty($account["employee_id"])) {

    $stmt = mysqli_prepare(
        $conn,
        "SELECT employees.*, departments.name AS department_name
         FROM employees
         LEFT JOIN departments ON employees.department_id = departments.id
         WHERE employees.id = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $account["employee_id"]);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $employee = $result ? mysqli_fetch_assoc($result) : null;

    mysqli_stmt_close($stmt);
}

$roleLabels = [
    "super_admin" => "Super Admin",
    "admin" => "Administrator",
    "hr_manager" => "HR Manager",
    "employee" => "Employee"
];

$roleLabel = $roleLabels[$account["role"]] ?? $account["role"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>

    <?php include __DIR__ . "/includes/tailwind.php"; ?>
</head>

<body class="min-h-screen bg-slate-100">

    <div class="mx-auto max-w-4xl px-4 py-10">

        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-cyan-700">Employee workspace</p>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">My Profile</h1>
            </div>
            <a href="Dashboard.php"
                class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                Back to Dashboard
            </a>
        </div>

        <?php if ($employee === null): ?>
            <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                Your login account is not linked to an employee record yet.
                Please ask an Administrator or HR Manager to link it from <strong>User Management</strong>.
                Until then, your account details are shown below.
            </div>
        <?php endif; ?>

        <!-- Account information -->
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Account information</h2>

            <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <p class="text-sm text-slate-500">Name</p>
                    <p class="mt-1 font-semibold text-slate-900"><?php echo htmlspecialchars($account["name"]); ?></p>
                </div>

                <div>
                    <p class="text-sm text-slate-500">Email</p>
                    <p class="mt-1 font-semibold text-slate-900"><?php echo htmlspecialchars($account["email"]); ?></p>
                </div>

                <div>
                    <p class="text-sm text-slate-500">Role</p>
                    <p class="mt-1 font-semibold text-slate-900"><?php echo htmlspecialchars($roleLabel); ?></p>
                </div>

                <div>
                    <p class="text-sm text-slate-500">Account status</p>
                    <p class="mt-1">
                        <?php if ($account["status"] === "Active") { ?>
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Active</span>
                        <?php } else { ?>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                <?php echo htmlspecialchars($account["status"]); ?>
                            </span>
                        <?php } ?>
                    </p>
                </div>

                <div>
                    <p class="text-sm text-slate-500">Member since</p>
                    <p class="mt-1 font-semibold text-slate-900"><?php echo htmlspecialchars($account["created_at"]); ?></p>
                </div>
            </div>
        </div>

        <?php if ($employee !== null): ?>
            <!-- Employee information -->
            <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Employee details</h2>

                <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <p class="text-sm text-slate-500">Employee Code</p>
                        <p class="mt-1 font-semibold text-slate-900"><?php echo htmlspecialchars($employee["employee_code"] ?? "N/A"); ?></p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Full Name</p>
                        <p class="mt-1 font-semibold text-slate-900">
                            <?php echo htmlspecialchars(($employee["first_name"] ?? "") . " " . ($employee["last_name"] ?? "")); ?>
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Email</p>
                        <p class="mt-1 font-semibold text-slate-900"><?php echo htmlspecialchars($employee["email"] ?? "N/A"); ?></p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Phone</p>
                        <p class="mt-1 font-semibold text-slate-900"><?php echo htmlspecialchars($employee["phone"] ?? "N/A"); ?></p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Gender</p>
                        <p class="mt-1 font-semibold text-slate-900"><?php echo htmlspecialchars($employee["gender"] ?? "N/A"); ?></p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Department</p>
                        <p class="mt-1 font-semibold text-slate-900"><?php echo htmlspecialchars($employee["department_name"] ?? "N/A"); ?></p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Position</p>
                        <p class="mt-1 font-semibold text-slate-900"><?php echo htmlspecialchars($employee["position"] ?? "N/A"); ?></p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Salary</p>
                        <p class="mt-1 font-semibold text-slate-900"><?php echo htmlspecialchars($employee["salary"] ?? "N/A"); ?></p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Hire Date</p>
                        <p class="mt-1 font-semibold text-slate-900"><?php echo htmlspecialchars($employee["hire_date"] ?? "N/A"); ?></p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Status</p>
                        <p class="mt-1 font-semibold text-slate-900"><?php echo htmlspecialchars($employee["status"] ?? "N/A"); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>
