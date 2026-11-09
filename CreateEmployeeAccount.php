<?php

require_once "AuthCheck.php";
checkRole(["admin", "hr_manager"]);

require_once "DataBaseConnetoin.php";

$message = "";
$error = "";

// Get employees who do not have a login account
$employees_sql = "
    SELECT id, employee_code, first_name, last_name, email
    FROM employees
    WHERE id NOT IN (
        SELECT employee_id
        FROM users
        WHERE employee_id IS NOT NULL
    )
    ORDER BY first_name ASC
";

$employees_result = mysqli_query($conn, $employees_sql);

if (!$employees_result) {
    die("Database Error: " . mysqli_error($conn));
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $employee_id = (int) $_POST["employee_id"];
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if ($employee_id <= 0 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please select an employee and enter a valid email.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {

        // Check if employee exists and has no account
        $check_sql = "
            SELECT id
            FROM employees
            WHERE id = ?
            AND id NOT IN (
                SELECT employee_id
                FROM users
                WHERE employee_id IS NOT NULL
            )
        ";

        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "i", $employee_id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);

        if (mysqli_num_rows($check_result) != 1) {
            $error = "Employee not found or already has an account.";
        } else {

            // Check duplicate email
            $email_sql = "SELECT id FROM users WHERE email = ?";
            $email_stmt = mysqli_prepare($conn, $email_sql);
            mysqli_stmt_bind_param($email_stmt, "s", $email);
            mysqli_stmt_execute($email_stmt);
            $email_result = mysqli_stmt_get_result($email_stmt);

            if (mysqli_num_rows($email_result) > 0) {
                $error = "This email already exists!";
            } else {

                // Get employee name
                $name_sql = "
                    SELECT CONCAT(first_name, ' ', last_name) AS full_name
                    FROM employees
                    WHERE id = ?
                ";

                $name_stmt = mysqli_prepare($conn, $name_sql);
                mysqli_stmt_bind_param($name_stmt, "i", $employee_id);
                mysqli_stmt_execute($name_stmt);
                $name_result = mysqli_stmt_get_result($name_stmt);
                $employee = mysqli_fetch_assoc($name_result);

                $name = $employee["full_name"];
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $role = "employee";

                // Insert user account
                $insert_sql = "
                    INSERT INTO users
                    (employee_id, name, email, PASSWORD, role)
                    VALUES (?, ?, ?, ?, ?)
                ";

                $insert_stmt = mysqli_prepare($conn, $insert_sql);

                mysqli_stmt_bind_param(
                    $insert_stmt,
                    "issss",
                    $employee_id,
                    $name,
                    $email,
                    $hashed_password,
                    $role
                );

                if (mysqli_stmt_execute($insert_stmt)) {
                    $message = "Employee account created successfully!";
                    
                    // Refresh employee dropdown
                    mysqli_stmt_close($insert_stmt);
                    mysqli_stmt_close($name_stmt);
                    mysqli_stmt_close($email_stmt);
                    mysqli_stmt_close($check_stmt);

                    header("Location: CreateEmployeeAccount.php?success=1");
                    exit();
                } else {
                    $error = "Account creation failed: " . mysqli_stmt_error($insert_stmt);
                }

                mysqli_stmt_close($insert_stmt);
                mysqli_stmt_close($name_stmt);
            }

            mysqli_stmt_close($email_stmt);
        }

        mysqli_stmt_close($check_stmt);
    }
}

if (isset($_GET["success"])) {
    $message = "Employee account created successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . "/includes/tailwind.php"; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Employee Account</title>
</head>
<body class="min-h-screen bg-slate-100">
    <div class="mx-auto max-w-2xl px-4 py-10">

        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-cyan-700">User Management</p>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Create Employee Login Account</h1>
            </div>
            <a href="Dashboard.php"
                class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                Back to Dashboard
            </a>
        </div>

        <?php if ($message != "") { ?>
            <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php } ?>

        <?php if ($error != "") { ?>
            <div class="mb-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php } ?>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <form method="POST" class="space-y-5">

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Select Employee</label>
                    <select name="employee_id" required
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                        <option value="">-- Select Employee --</option>
                        <?php while ($employee = mysqli_fetch_assoc($employees_result)) { ?>
                            <option value="<?php echo $employee["id"]; ?>">
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
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" name="email" required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Password</label>
                    <input type="password" name="password" required minlength="6"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                    <p class="mt-1 text-xs text-slate-500">At least 6 characters.</p>
                </div>

                <button type="submit"
                    class="w-full rounded-lg bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-700 sm:w-auto">
                    Create Account
                </button>

            </form>
        </div>

    </div>
</body>
</html>
