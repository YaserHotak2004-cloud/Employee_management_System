
<?php
require_once "AuthCheck.php";
require_once "DataBaseConnetoin.php";

checkLogin();

$role = $_SESSION["user_role"];
$user_id = $_SESSION["user_id"];

$isManager = in_array($role, ["admin", "hr_manager", "super_admin"]);

$message = "";

// Register attendance (Admin and HR Manager only)
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    checkRole(["admin", "hr_manager"]);

    $employee_id = (int)($_POST["employee_id"] ?? 0);
    $attendance_date = $_POST["attendance_date"] ?? "";
    $status = $_POST["status"] ?? "";
    $check_in = !empty($_POST["check_in"]) ? $_POST["check_in"] : null;
    $check_out = !empty($_POST["check_out"]) ? $_POST["check_out"] : null;

    $allowedStatuses = ["Present", "Absent", "Late", "on leave"];

    if (
        $employee_id <= 0 ||
        !DateTime::createFromFormat("Y-m-d", $attendance_date) ||
        !in_array($status, $allowedStatuses, true)
    ) {
        $message = "Please enter valid attendance information.";
    } else {

        // Check whether employee exists
        $check = mysqli_prepare(
            $conn,
            "SELECT id FROM employees WHERE id = ?"
        );

        mysqli_stmt_bind_param($check, "i", $employee_id);
        mysqli_stmt_execute($check);
        $checkResult = mysqli_stmt_get_result($check);

        if (mysqli_num_rows($checkResult) === 0) {
            $message = "Employee not found!";
        } else {

            $sql = "INSERT INTO attendance
                    (employee_id, attendance_date, status, check_in, check_out)
                    VALUES (?, ?, ?, ?, ?)";

            $stmt = mysqli_prepare($conn, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "issss",
                $employee_id,
                $attendance_date,
                $status,
                $check_in,
                $check_out
            );

            if (mysqli_stmt_execute($stmt)) {
                header("Location: Attendance.php?success=1");
                exit();
            } else {
                if (mysqli_errno($conn) == 1062) {
                    $message = "Attendance is already recorded for this employee on this date.";
                } else {
                    $message = "Error: " . mysqli_error($conn);
                }
            }
        }
    }
}

if (isset($_GET["success"])) {
    $message = "Attendance recorded successfully!";
}

// Fetch employees for manager dropdown
$employees = null;

if ($isManager) {
    $employees = mysqli_query(
        $conn,
        "SELECT id, employee_code, first_name, last_name
         FROM employees
         ORDER BY first_name ASC"
    );
}

// Fetch attendance records
if ($isManager) {

    $sql = "SELECT
                attendance.*,
                employees.first_name,
                employees.last_name,
                employees.employee_code
            FROM attendance
            INNER JOIN employees
                ON attendance.employee_id = employees.id
            ORDER BY attendance.attendance_date DESC,
                     attendance.id DESC";

    $result = mysqli_query($conn, $sql);

} else {

    $sql = "SELECT
                attendance.*,
                employees.first_name,
                employees.last_name,
                employees.employee_code
            FROM users
            INNER JOIN employees
                ON users.employee_id = employees.id
            INNER JOIN attendance
                ON attendance.employee_id = employees.id
            WHERE users.id = ?
            ORDER BY attendance.attendance_date DESC";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management</title>
    <?php include __DIR__ . "/includes/tailwind.php"; ?>
</head>

<body class="bg-gray-100 min-h-screen p-6">

<div class="max-w-6xl mx-auto">

    <h1 class="text-3xl font-bold text-gray-800 mb-6">
        Attendance Management
    </h1>

    <?php if ($message): ?>
        <div class="mb-5 rounded-lg bg-blue-100 text-blue-800 p-4">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($isManager): ?>

    <div class="bg-white p-6 rounded-xl shadow-sm mb-8">

        <h2 class="text-xl font-bold mb-5">
            Record Employee Attendance
        </h2>

        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-5">

            <div>
                <label class="block mb-2 font-medium">Employee</label>

                <select name="employee_id" required
                    class="w-full border border-slate-300 rounded-lg p-3">

                    <option value="">Select Employee</option>

                    <?php while ($emp = mysqli_fetch_assoc($employees)): ?>

                        <option value="<?= $emp["id"] ?>">
                            <?= htmlspecialchars(
                                $emp["employee_code"] . " - " .
                                $emp["first_name"] . " " .
                                $emp["last_name"]
                            ) ?>
                        </option>

                    <?php endwhile; ?>

                </select>
            </div>

            <div>
                <label class="block mb-2 font-medium">Date</label>

                <input type="date" name="attendance_date"
                    value="<?= date('Y-m-d') ?>" required
                    class="w-full border border-slate-300 rounded-lg p-3">
            </div>

            <div>
                <label class="block mb-2 font-medium">Status</label>

                <select name="status" required
                    class="w-full border border-slate-300 rounded-lg p-3">

                    <option value="Present">Present</option>
                    <option value="Absent">Absent</option>
                    <option value="Late">Late</option>
                    <option value="on leave">On Leave</option>

                </select>
            </div>

            <div>
                <label class="block mb-2 font-medium">Check In</label>

                <input type="time" name="check_in"
                    class="w-full border border-slate-300 rounded-lg p-3">
            </div>

            <div>
                <label class="block mb-2 font-medium">Check Out</label>

                <input type="time" name="check_out"
                    class="w-full border border-slate-300 rounded-lg p-3">
            </div>

            <div class="flex items-end">
                <button type="submit"
                    class="w-full bg-blue-600 text-white p-3 rounded-lg hover:bg-blue-700">
                    Save Attendance
                </button>
            </div>

        </form>

    </div>

    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-sm p-6 overflow-x-auto">

        <h2 class="text-xl font-bold mb-5">
            <?= $isManager ? "All Attendance Records" : "My Attendance Records" ?>
        </h2>

        <table class="w-full text-left border-collapse">

            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3">Employee</th>
                    <th class="p-3">Date</th>
                    <th class="p-3">Status</th>
                    <th class="p-3">Check In</th>
                    <th class="p-3">Check Out</th>
                </tr>
            </thead>

            <tbody>

                <?php if (mysqli_num_rows($result) > 0): ?>

                    <?php while ($row = mysqli_fetch_assoc($result)): ?>

                        <tr class="border-b border-slate-200">

                            <td class="p-3">
                                <?= htmlspecialchars(
                                    $row["first_name"] . " " .
                                    $row["last_name"]
                                ) ?>
                            </td>

                            <td class="p-3">
                                <?= htmlspecialchars($row["attendance_date"]) ?>
                            </td>

                            <td class="p-3">
                                <?= htmlspecialchars($row["status"]) ?>
                            </td>

                            <td class="p-3">
                                <?= htmlspecialchars($row["check_in"] ?? "—") ?>
                            </td>

                            <td class="p-3">
                                <?= htmlspecialchars($row["check_out"] ?? "—") ?>
                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="5" class="p-5 text-center text-gray-500">
                            No attendance records found.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

    <div class="mt-6">
        <a href="Dashboard.php"
           class="bg-gray-800 text-white px-5 py-3 rounded-lg hover:bg-white hover:text-black transition">
            Back to Dashboard
        </a>
    </div>

</div>

</body>
</html>
