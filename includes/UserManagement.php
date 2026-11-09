<?php
require_once __DIR__ . "/auth.php";

$currentRole = $_SESSION["user_role"] ?? "";

if (!in_array($currentRole, ["admin", "super_admin"], true)) {
    http_response_code(403);
    die("Access Denied: Admin only.");
}

$message = "";

$roleLabels = [
    "super_admin" => "Super Admin",
    "admin" => "Administrator",
    "hr_manager" => "HR Manager",
    "employee" => "Employee"
];

$allowedRoles = $currentRole === "super_admin"
    ? ["super_admin", "admin", "hr_manager", "employee"]
    : ["admin", "hr_manager", "employee"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = (int) $_POST["user_id"];
    $role = $_POST["role"] ?? "";
    $status = $_POST["status"] ?? "";
    $employee_id = (int) ($_POST["employee_id"] ?? 0);

    $allowedStatuses = ["Active", "Inactive"];

    // Load the target account
    $targetStmt = mysqli_prepare($conn, "SELECT id, role FROM users WHERE id = ?");
    mysqli_stmt_bind_param($targetStmt, "i", $user_id);
    mysqli_stmt_execute($targetStmt);
    $targetResult = mysqli_stmt_get_result($targetStmt);
    $target = $targetResult ? mysqli_fetch_assoc($targetResult) : null;
    mysqli_stmt_close($targetStmt);

    if (!$target) {

        $message = "User not found.";

    } elseif (!in_array($status, $allowedStatuses, true)) {

        $message = "Invalid status.";

    } elseif ($target["role"] === "super_admin" && $currentRole !== "super_admin") {

        $message = "Only a Super Admin can change a Super Admin account.";

    } elseif ($role === "super_admin" && $currentRole !== "super_admin") {

        $message = "Only a Super Admin can assign the Super Admin role.";

    } elseif (!in_array($role, $allowedRoles, true)) {

        $message = "Invalid role or status.";

    } elseif ($user_id == $_SESSION["user_id"] && $status === "Inactive") {

        $message = "You cannot deactivate your own account.";

    } elseif ($user_id == $_SESSION["user_id"] && $target["role"] === "super_admin" && $role !== "super_admin") {

        $message = "You cannot remove your own Super Admin role.";

    } elseif ($role === "employee" && $employee_id <= 0) {

        $message = "Please select the employee record for this employee account.";

    } else {

        $employeeError = false;

        if ($role === "employee") {

            // The selected employee must exist and must not be linked to another account.
            $checkStmt = mysqli_prepare(
                $conn,
                "SELECT id FROM users WHERE employee_id = ? AND id <> ?"
            );

            mysqli_stmt_bind_param($checkStmt, "ii", $employee_id, $user_id);
            mysqli_stmt_execute($checkStmt);
            mysqli_stmt_store_result($checkStmt);

            if (mysqli_stmt_num_rows($checkStmt) > 0) {
                $message = "This employee record is already linked to another account.";
                $employeeError = true;
            }

            mysqli_stmt_close($checkStmt);

        } else {

            $employee_id = null;
        }

        if (!$employeeError) {

            $sql = "UPDATE users SET role = ?, status = ?, employee_id = ? WHERE id = ?";

            $stmt = mysqli_prepare($conn, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "ssii",
                $role,
                $status,
                $employee_id,
                $user_id
            );

            if (mysqli_stmt_execute($stmt)) {
                $message = "User updated successfully!";
            } else {
                $message = "Could not update user: " . mysqli_stmt_error($stmt);
            }

            mysqli_stmt_close($stmt);
        }
    }
}

$sql = "SELECT id, name, email, role, status, employee_id, created_at
        FROM users ORDER BY id ASC";

$result = mysqli_query($conn, $sql);

$users = [];

while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}

// Employee records map
$employees = [];
$employeesResult = mysqli_query(
    $conn,
    "SELECT id, employee_code, first_name, last_name FROM employees ORDER BY first_name"
);

while ($employee = mysqli_fetch_assoc($employeesResult)) {
    $employees[(int) $employee["id"]] = $employee;
}

// Which employee record is linked to which user account
$linkedBy = [];

foreach ($users as $row) {
    if (!empty($row["employee_id"])) {
        $linkedBy[(int) $row["employee_id"]] = (int) $row["id"];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>User Management - NexaTech HR</title>

    <?php include __DIR__ . "/tailwind.php"; ?>
</head>

<body class="bg-slate-100">

<div class="flex min-h-screen">

    <?php include __DIR__ . "/sidebar.php"; ?>

    <div class="flex-1">

        <?php include __DIR__ . "/Navbar.php"; ?>

        <main class="p-6">

            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold">User Management</h1>
                    <?php if ($currentRole === "super_admin"): ?>
                        <p class="mt-1 text-sm text-slate-500">Super Admin: full control over all accounts and roles.</p>
                    <?php endif; ?>
                </div>
                <a href="../Dashboard.php"
                    class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Back to Dashboard
                </a>
            </div>

            <?php if ($message != ""): ?>
                <div class="bg-blue-100 text-blue-800 p-3 rounded-lg mb-5">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-sm p-5 overflow-x-auto">

                <table class="w-full text-left text-sm">

                    <thead class="bg-slate-100">
                        <tr>
                            <th class="p-3">ID</th>
                            <th class="p-3">Name</th>
                            <th class="p-3">Email</th>
                            <th class="p-3">Employee record</th>
                            <th class="p-3">Role</th>
                            <th class="p-3">Status</th>
                            <th class="p-3">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php foreach ($users as $user): ?>

                        <?php $formId = "user-form-" . (int) $user["id"]; ?>

                        <tr class="border-b border-slate-200">

                            <td class="p-3">
                                <?php echo (int) $user["id"]; ?>
                            </td>

                            <td class="p-3">
                                <?php echo htmlspecialchars($user["name"]); ?>
                            </td>

                            <td class="p-3">
                                <?php echo htmlspecialchars($user["email"]); ?>
                            </td>

                            <td class="p-3">
                                <select name="employee_id" form="<?php echo $formId; ?>"
                                        class="border border-slate-300 rounded-sm p-2">
                                    <option value="0">— none —</option>
                                    <?php foreach ($employees as $empId => $employee): ?>
                                        <?php
                                        $isLinkedElsewhere =
                                            isset($linkedBy[$empId]) &&
                                            $linkedBy[$empId] !== (int) $user["id"];
                                        ?>
                                        <option
                                            value="<?php echo $empId; ?>"
                                            <?php if ((int) $user["employee_id"] === $empId) echo "selected"; ?>
                                            <?php if ($isLinkedElsewhere) echo "disabled"; ?>
                                        >
                                            <?php
                                            echo htmlspecialchars(
                                                $employee["employee_code"] . " - " .
                                                $employee["first_name"] . " " .
                                                $employee["last_name"]
                                            );
                                            ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>

                            <td class="p-3">

                                <select name="role" form="<?php echo $formId; ?>"
                                        class="border border-slate-300 rounded-sm p-2">

                                    <?php foreach ($allowedRoles as $roleOption): ?>
                                        <option value="<?php echo $roleOption; ?>"
                                            <?php if ($user["role"] === $roleOption) echo "selected"; ?>>
                                            <?php echo htmlspecialchars($roleLabels[$roleOption] ?? $roleOption); ?>
                                        </option>
                                    <?php endforeach; ?>

                                </select>

                            </td>

                            <td class="p-3">

                                <select name="status" form="<?php echo $formId; ?>"
                                        class="border border-slate-300 rounded-sm p-2">

                                    <option value="Active"
                                        <?php if ($user["status"] === "Active") echo "selected"; ?>>
                                        Active
                                    </option>

                                    <option value="Inactive"
                                        <?php if ($user["status"] === "Inactive") echo "selected"; ?>>
                                        Inactive
                                    </option>

                                </select>

                            </td>

                            <td class="p-3">

                                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="<?php echo $formId; ?>">

                                    <input type="hidden"
                                           name="user_id"
                                           value="<?php echo (int) $user["id"]; ?>">

                                    <button type="submit"
                                            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                                        Update
                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

            <p class="mt-4 text-xs text-slate-500">
                Employee accounts must be linked to an employee record so the employee can open their own profile.
            </p>

        </main>

    </div>

</div>

</body>
</html>
