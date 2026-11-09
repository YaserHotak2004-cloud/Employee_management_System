
<?php
require_once "AuthCheck.php";
require_once "DataBaseConnetoin.php";

checkLogin();

$role = $_SESSION["user_role"];
$isManager = in_array($role, ["admin", "hr_manager", "super_admin"]);
$message = "";

// Add monthly salary
if ($_SERVER["REQUEST_METHOD"] === "POST" && $isManager) {

    if (isset($_POST["add_salary"])) {

        $employee_id = (int) $_POST["employee_id"];
        $payment_month = $_POST["payment_month"];
        $payment_year = (int) $_POST["payment_year"];
        $allowance = (float) $_POST["allowance"];
        $deduction = (float) $_POST["deduction"];

        // Get employee basic salary
        $sql = "SELECT salary FROM employees WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $employee_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $employee = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$employee) {
            $message = "Employee not found!";
        } elseif (
            $allowance < 0 ||
            $deduction < 0 ||
            $payment_year < 2000 ||
            $payment_year > 2100
        ) {
            $message = "Invalid salary information!";
        } else {

            $basic_salary = (float) $employee["salary"];
            $net_salary = $basic_salary + $allowance - $deduction;

            if ($net_salary < 0) {
                $message = "Net salary cannot be negative!";
            } else {

                $sql = "INSERT INTO salary
                    (employee_id, basic_salary, allowance, deduction,
                     net_salary, payment_month, payment_year, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')";

                $stmt = mysqli_prepare($conn, $sql);

                mysqli_stmt_bind_param(
                    $stmt,
                    "iddddsi",
                    $employee_id,
                    $basic_salary,
                    $allowance,
                    $deduction,
                    $net_salary,
                    $payment_month,
                    $payment_year
                );

                if (mysqli_stmt_execute($stmt)) {
                    $message = "Salary added successfully!";
                } else {
                    $message = "Error: " . mysqli_stmt_error($stmt);
                }

                mysqli_stmt_close($stmt);
            }
        }
    }

    // Update payment status
    if (isset($_POST["update_status"])) {

        $salary_id = (int) $_POST["salary_id"];
        $new_status = $_POST["new_status"];

        if (in_array($new_status, ["Pending", "Paid"])) {

            $sql = "UPDATE salary
                    SET status = ?,
                        payment_date = CASE
                            WHEN ? = 'Paid' THEN CURDATE()
                            ELSE NULL
                        END
                    WHERE id = ?";

            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param(
                $stmt,
                "ssi",
                $new_status,
                $new_status,
                $salary_id
            );

            if (mysqli_stmt_execute($stmt)) {
                $message = "Payment status updated!";
            }

            mysqli_stmt_close($stmt);
        }
    }
}

// Get employees for manager dropdown
if ($isManager) {
    $employee_sql = "SELECT id, employee_code, first_name,
                            last_name, salary
                     FROM employees
                     ORDER BY first_name ASC";

    $employee_result = mysqli_query($conn, $employee_sql);
}

// Get salary records
if ($isManager) {

    $sql = "SELECT salary.*, employees.employee_code,
                   employees.first_name, employees.last_name
            FROM salary
            INNER JOIN employees
            ON salary.employee_id = employees.id
            ORDER BY payment_year DESC, payment_month DESC";

    $result = mysqli_query($conn, $sql);

} else {

    $user_id = $_SESSION["user_id"];

    $sql = "SELECT salary.*, employees.employee_code,
                   employees.first_name, employees.last_name
            FROM salary
            INNER JOIN employees
            ON salary.employee_id = employees.id
            INNER JOIN users
            ON users.employee_id = employees.id
            WHERE users.id = ?
            ORDER BY payment_year DESC, payment_month DESC";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
}

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Management</title>
    <?php include __DIR__ . "/includes/tailwind.php"; ?>
</head>

<body class="bg-gray-100 min-h-screen p-6">

<div class="max-w-7xl mx-auto">

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-3xl font-bold text-gray-800">
            Salary Management
        </h1>
        <a href="Dashboard.php"
            class="rounded-lg border border-slate-300 bg-gray-800  px-4 py-2 text-sm font-semibold text-white  transition hover:bg-white hover:text-black">
            Back to Dashboard
        </a>
    </div>

    <?php if ($message): ?>
        <div class="bg-blue-100 text-blue-800 p-3 rounded-sm mb-5">
            <?= e($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($isManager): ?>

    <div class="bg-white p-6 rounded-xl shadow-sm mb-8">

        <h2 class="text-xl font-semibold mb-5">
            Add Monthly Salary
        </h2>

        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <input type="hidden" name="add_salary" value="1">

            <div>
                <label class="block mb-2 font-medium">
                    Select Employee
                </label>

                <select name="employee_id" required
                    class="w-full border border-slate-300 rounded-lg p-3">

                    <option value="">Choose Employee</option>

                    <?php while ($emp = mysqli_fetch_assoc($employee_result)): ?>

                        <option value="<?= e($emp["id"]) ?>">
                            <?= e($emp["employee_code"]) ?> -
                            <?= e($emp["first_name"] . " " . $emp["last_name"]) ?>
                            (<?= e($emp["salary"]) ?>)
                        </option>

                    <?php endwhile; ?>

                </select>
            </div>

            <div>
                <label class="block mb-2 font-medium">
                    Payment Month
                </label>

                <select name="payment_month" required
                    class="w-full border border-slate-300 rounded-lg p-3">

                    <option value="">Select Month</option>

                    <?php
                    $months = [
                        "January", "February", "March", "April",
                        "May", "June", "July", "August",
                        "September", "October", "November", "December"
                    ];

                    foreach ($months as $month):
                    ?>
                        <option value="<?= $month ?>">
                            <?= $month ?>
                        </option>
                    <?php endforeach; ?>

                </select>
            </div>

            <div>
                <label class="block mb-2 font-medium">
                    Payment Year
                </label>

                <input type="number" name="payment_year"
                    value="<?= date("Y") ?>"
                    min="2000" max="2100" required
                    class="w-full border border-slate-300 rounded-lg p-3">
            </div>

            <div>
                <label class="block mb-2 font-medium">
                    Allowance
                </label>

                <input type="number" name="allowance"
                    value="0" min="0" step="0.01" required
                    class="w-full border border-slate-300 rounded-lg p-3">
            </div>

            <div>
                <label class="block mb-2 font-medium">
                    Deduction
                </label>

                <input type="number" name="deduction"
                    value="0" min="0" step="0.01" required
                    class="w-full border border-slate-300 rounded-lg p-3">
            </div>

            <div class="flex items-end">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white
                    px-6 py-3 rounded-lg w-full">
                    Add Salary
                </button>
            </div>

        </form>
    </div>

    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-sm overflow-x-auto">

        <h2 class="text-xl font-semibold p-5">
            Salary Records
        </h2>

        <table class="w-full text-left border-collapse">

            <thead class="bg-gray-200">
                <tr>
                    <th class="p-3">Employee</th>
                    <th class="p-3">Month</th>
                    <th class="p-3">Year</th>
                    <th class="p-3">Basic</th>
                    <th class="p-3">Allowance</th>
                    <th class="p-3">Deduction</th>
                    <th class="p-3">Net Salary</th>
                    <th class="p-3">Status</th>

                    <?php if ($isManager): ?>
                        <th class="p-3">Action</th>
                    <?php endif; ?>
                </tr>
            </thead>

            <tbody>

            <?php if (mysqli_num_rows($result) > 0): ?>

                <?php while ($row = mysqli_fetch_assoc($result)): ?>

                    <tr class="border-b border-slate-200 hover:bg-gray-50">

                        <td class="p-3">
                            <?= e($row["first_name"] . " " . $row["last_name"]) ?>
                        </td>

                        <td class="p-3">
                            <?= e($row["payment_month"]) ?>
                        </td>

                        <td class="p-3">
                            <?= e($row["payment_year"]) ?>
                        </td>

                        <td class="p-3">
                            <?= e($row["basic_salary"]) ?>
                        </td>

                        <td class="p-3">
                            <?= e($row["allowance"]) ?>
                        </td>

                        <td class="p-3">
                            <?= e($row["deduction"]) ?>
                        </td>

                        <td class="p-3 font-bold">
                            <?= e($row["net_salary"]) ?>
                        </td>

                        <td class="p-3">
                            <span class="px-3 py-1 rounded-full text-sm
                                <?= $row["status"] === "Paid"
                                    ? "bg-green-100 text-green-700"
                                    : "bg-yellow-100 text-yellow-700" ?>">
                                <?= e($row["status"]) ?>
                            </span>
                        </td>

                        <?php if ($isManager): ?>

                        <td class="p-3">

                            <form method="POST" class="flex gap-2">

                                <input type="hidden"
                                    name="update_status" value="1">

                                <input type="hidden"
                                    name="salary_id"
                                    value="<?= e($row["id"]) ?>">

                                <select name="new_status"
                                    class="border border-slate-300 rounded-sm p-2">

                                    <option value="Pending"
                                        <?= $row["status"] === "Pending" ? "selected" : "" ?>>
                                        Pending
                                    </option>

                                    <option value="Paid"
                                        <?= $row["status"] === "Paid" ? "selected" : "" ?>>
                                        Paid
                                    </option>

                                </select>

                                <button type="submit"
                                    class="bg-blue-600 text-white px-3 py-2 rounded-sm">
                                    Update
                                </button>

                            </form>

                        </td>

                        <?php endif; ?>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>
                    <td colspan="<?= $isManager ? 9 : 8 ?>"
                        class="text-center p-6 text-gray-500">
                        No salary records found.
                    </td>
                </tr>

            <?php endif; ?>

            </tbody>
        </table>

    </div>

</div>

</body>
</html>
