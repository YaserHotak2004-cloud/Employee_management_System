<?php
require_once "AuthCheck.php";

checkRole(["admin", "hr_manager"]);

require_once "DataBaseConnetoin.php";

$message = "";
$success = false;

$currentRole = $_SESSION["user_role"] ?? "";

// Super Admin and Admin can create any standard account; HR Manager can only create employee accounts.
$allowedRoles = in_array($currentRole, ["admin", "super_admin"], true)
    ? ["admin", "hr_manager", "employee"]
    : ["employee"];

$roleLabels = [
    "super_admin" => "Super Admin",
    "admin" => "Administrator",
    "hr_manager" => "HR Manager",
    "employee" => "Employee"
];

// Employee records that do not have a login account yet.
$availableEmployees = [];

$employeesResult = mysqli_query(
    $conn,
    "SELECT id, employee_code, first_name, last_name
     FROM employees
     WHERE id NOT IN (
         SELECT employee_id FROM users WHERE employee_id IS NOT NULL
     )
     ORDER BY first_name"
);

while ($employee = mysqli_fetch_assoc($employeesResult)) {
    $availableEmployees[(int) $employee["id"]] = $employee;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $role = $_POST["role"] ?? "employee";
    $employee_id = (int) ($_POST["employee_id"] ?? 0);

    $employeeError = "";

    if ($role === "employee") {

        if ($employee_id <= 0) {
            $employeeError = "Please select the employee record for this account.";
        } elseif (!isset($availableEmployees[$employee_id])) {
            $employeeError = "That employee record is not available (it may already have an account).";
        }
    }

    if (!in_array($role, $allowedRoles, true)) {

        $message = "You are not allowed to create an account with this role.";

    } elseif (strlen($password) < 8) {

        $message = "Password must be at least 8 characters.";

    } elseif ($employeeError !== "") {

        $message = $employeeError;

    } else {

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        if ($role === "employee") {
            $linkEmployeeId = $employee_id;
        } else {
            $linkEmployeeId = null;
        }

        $sql = "INSERT INTO users (employee_id, name, email, PASSWORD, role)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "issss",
            $linkEmployeeId,
            $name,
            $email,
            $hashed_password,
            $role
        );

        if (mysqli_stmt_execute($stmt)) {
            $message = "User registered successfully!";
            $success = true;

            // Refresh the list of employees without an account.
            $availableEmployees = [];

            $refreshResult = mysqli_query(
                $conn,
                "SELECT id, employee_code, first_name, last_name
                 FROM employees
                 WHERE id NOT IN (
                     SELECT employee_id FROM users WHERE employee_id IS NOT NULL
                 )
                 ORDER BY first_name"
            );

            while ($employee = mysqli_fetch_assoc($refreshResult)) {
                $availableEmployees[(int) $employee["id"]] = $employee;
            }
        } else {
            $message = "Registration failed: " . mysqli_error($conn);
        }

        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . "/includes/tailwind.php"; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register User</title>
</head>
<body class="min-h-screen bg-slate-100">
    <div class="flex min-h-screen items-center justify-center px-4 py-10">
        <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">

            <div class="mb-6 text-center">
                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-500 text-xl font-black text-slate-950">N</div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Register New User</h1>
                <p class="mt-1 text-sm text-slate-500">Create an account for NexaTech HR</p>
            </div>

            <?php if ($message !== "") { ?>
                <?php if ($success) { ?>
                    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php } else { ?>
                    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php } ?>
            <?php } ?>

            <form method="POST" class="space-y-4">
                <div>
                    <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Name</label>
                    <input type="text" id="name" name="name" required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                </div>

                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" id="email" name="email" required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                </div>

                <div>
                    <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Password</label>
                    <input type="password" id="password" name="password" required minlength="8"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                    <p class="mt-1 text-xs text-slate-500">At least 8 characters.</p>
                </div>

                <div>
                    <label for="role" class="mb-1 block text-sm font-medium text-slate-700">Role</label>
                    <select id="role" name="role" required
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                        <?php foreach ($allowedRoles as $roleOption) { ?>
                            <option value="<?php echo $roleOption; ?>">
                                <?php echo htmlspecialchars($roleLabels[$roleOption] ?? $roleOption); ?>
                            </option>
                        <?php } ?>
                    </select>
                    <?php if ($currentRole !== "admin" && $currentRole !== "super_admin") { ?>
                        <p class="mt-1 text-xs text-slate-500">HR Managers can only create employee accounts.</p>
                    <?php } ?>
                </div>

                <div id="employeeLinkField">
                    <label for="employee_id" class="mb-1 block text-sm font-medium text-slate-700">Employee record</label>
                    <select id="employee_id" name="employee_id"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                        <option value="0">— Select employee —</option>
                        <?php foreach ($availableEmployees as $empId => $employee) { ?>
                            <option value="<?php echo $empId; ?>">
                                <?php
                                echo htmlspecialchars(
                                    $employee["employee_code"] . " - " .
                                    $employee["first_name"] . " " .
                                    $employee["last_name"]
                                );
                                ?>
                            </option>
                        <?php } ?>
                    </select>
                    <p class="mt-1 text-xs text-slate-500">
                        Required for Employee accounts so the employee can see their own profile.
                    </p>
                    <?php if (count($availableEmployees) === 0) { ?>
                        <p class="mt-1 text-xs font-medium text-amber-700">
                            No employee records without an account. Add an employee first.
                        </p>
                    <?php } ?>
                </div>

                <button type="submit"
                    class="w-full rounded-lg bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-700">
                    Register
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-500">
                <a href="Dashboard.php" class="font-semibold text-cyan-700 hover:underline">Back to Dashboard</a>
            </p>

        </div>
    </div>

    <script>
        (function () {
            var roleSelect = document.getElementById("role");
            var linkField = document.getElementById("employeeLinkField");

            function syncEmployeeField() {
                linkField.style.display = roleSelect.value === "employee" ? "" : "none";
            }

            roleSelect.addEventListener("change", syncEmployeeField);
            syncEmployeeField();
        })();
    </script>
</body>
</html>
