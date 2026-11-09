
<?php
require_once "AuthCheck.php";
require_once "DataBaseConnetoin.php";

checkLogin();

$role = $_SESSION["user_role"];
$user_id = $_SESSION["user_id"];

$isManager = in_array($role, ["admin", "hr_manager", "super_admin"]);

$message = "";

// Handle employee leave request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit_leave"])) {

    checkRole(["employee"]);

    $employeeQuery = mysqli_prepare(
        $conn,
        "SELECT employee_id FROM users WHERE id = ?"
    );

    mysqli_stmt_bind_param($employeeQuery, "i", $user_id);
    mysqli_stmt_execute($employeeQuery);

    $employeeResult = mysqli_stmt_get_result($employeeQuery);
    $employeeData = mysqli_fetch_assoc($employeeResult);

    $employee_id = (int)($employeeData["employee_id"] ?? 0);

    $leave_type = $_POST["leave_type"] ?? "";
    $start_date = $_POST["start_date"] ?? "";
    $end_date = $_POST["end_date"] ?? "";
    $reason = trim($_POST["reason"] ?? "");

    $allowedTypes = ["sick", "Casual", "Annual", "other"];

    $start = DateTime::createFromFormat("!Y-m-d", $start_date);
    $end = DateTime::createFromFormat("!Y-m-d", $end_date);

    if ($employee_id <= 0) {
        $message = "Your account is not linked to an employee record.";
    } elseif (
        !in_array($leave_type, $allowedTypes, true) ||
        !$start || $start->format("Y-m-d") !== $start_date ||
        !$end || $end->format("Y-m-d") !== $end_date ||
        $end < $start
    ) {
        $message = "Please enter valid leave information.";
    } else {

        $sql = "INSERT INTO leave_requests
                (employee_id, leave_type, start_date, end_date, reason)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "issss",
            $employee_id,
            $leave_type,
            $start_date,
            $end_date,
            $reason
        );

        if (mysqli_stmt_execute($stmt)) {
            header("Location: LeaveManagement.php?success=1");
            exit();
        } else {
            $message = "Unable to submit leave request.";
        }
    }
}


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["review_leave"])) {

    checkRole(["admin", "hr_manager"]);

    $leave_id = (int)($_POST["leave_id"] ?? 0);
    $new_status = $_POST["new_status"] ?? "";

    if (
        $leave_id <= 0 ||
        !in_array($new_status, ["Approved", "Rejected"], true)
    ) {
        $message = "Invalid review information.";
    } else {

        $sql = "UPDATE leave_requests
                SET status = ?,
                    reviewed_by = ?,
                    reviewed_at = NOW()
                WHERE id = ?
                  AND status = 'Pending'";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "sii",
            $new_status,
            $user_id,
            $leave_id
        );

        if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
            header("Location: LeaveManagement.php?reviewed=1");
            exit();
        } else {
            $message = "Request not found or already reviewed.";
        }
    }
}

if (isset($_GET["success"])) {
    $message = "Leave request submitted successfully!";
}

if (isset($_GET["reviewed"])) {
    $message = "Leave request reviewed successfully!";
}

// Fetch leave requests
if ($isManager) {

    $sql = "SELECT
                leave_requests.*,
                employees.first_name,
                employees.last_name,
                users.name AS reviewer_name
            FROM leave_requests
            INNER JOIN employees
                ON leave_requests.employee_id = employees.id
            LEFT JOIN users
                ON leave_requests.reviewed_by = users.id
            ORDER BY leave_requests.created_at DESC";

    $result = mysqli_query($conn, $sql);

} else {

    $sql = "SELECT *
            FROM leave_requests
            WHERE employee_id = (
                SELECT employee_id
                FROM users
                WHERE id = ?
            )
            ORDER BY created_at DESC";

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

    <title>Leave Management</title>

    <?php include __DIR__ . "/includes/tailwind.php"; ?>
</head>

<body class="bg-gray-100 min-h-screen p-6">

<div class="max-w-6xl mx-auto">

    <h1 class="text-3xl font-bold text-gray-800 mb-6">
        Leave Management System
    </h1>

    <?php if ($message): ?>
        <div class="bg-blue-100 text-blue-800 p-4 rounded-lg mb-6">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if (!$isManager): ?>

    <div class="bg-white p-6 rounded-xl shadow-sm mb-8">

        <h2 class="text-xl font-bold mb-5">
            Submit Leave Request
        </h2>

        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-5">

            <div>
                <label class="block mb-2 font-medium">Leave Type</label>

                <select name="leave_type" required
                    class="w-full border border-slate-300 rounded-lg p-3">

                    <option value="Casual">Casual</option>
                    <option value="Annual">Annual</option>
                    <option value="sick">Sick</option>
                    <option value="other">Other</option>

                </select>
            </div>

            <div>
                <label class="block mb-2 font-medium">Start Date</label>

                <input type="date" name="start_date" required
                    class="w-full border border-slate-300 rounded-lg p-3">
            </div>

            <div>
                <label class="block mb-2 font-medium">End Date</label>

                <input type="date" name="end_date" required
                    class="w-full border border-slate-300 rounded-lg p-3">
            </div>

            <div class="md:col-span-2">
                <label class="block mb-2 font-medium">Reason</label>

                <textarea name="reason" rows="4"
                    class="w-full border border-slate-300 rounded-lg p-3"
                    placeholder="Write your reason..."></textarea>
            </div>

            <div>
                <button type="submit" name="submit_leave"
                    class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">
                    Submit Request
                </button>
            </div>

        </form>

    </div>

    <?php endif; ?>

    <div class="bg-white p-6 rounded-xl shadow-sm overflow-x-auto">

        <h2 class="text-xl font-bold mb-5">
            <?= $isManager ? "All Leave Requests" : "My Leave Requests" ?>
        </h2>

        <table class="w-full text-left border-collapse">

            <thead class="bg-gray-100">
                <tr>
                    <?php if ($isManager): ?>
                        <th class="p-3">Employee</th>
                    <?php endif; ?>

                    <th class="p-3">Leave Type</th>
                    <th class="p-3">Start Date</th>
                    <th class="p-3">End Date</th>
                    <th class="p-3">Reason</th>
                    <th class="p-3">Status</th>

                    <?php if ($isManager): ?>
                        <th class="p-3">Action</th>
                    <?php endif; ?>
                </tr>
            </thead>

            <tbody>

            <?php if ($result && mysqli_num_rows($result) > 0): ?>

                <?php while ($row = mysqli_fetch_assoc($result)): ?>

                    <tr class="border-b border-slate-200">

                        <?php if ($isManager): ?>
                            <td class="p-3">
                                <?= htmlspecialchars(
                                    $row["first_name"] . " " . $row["last_name"]
                                ) ?>
                            </td>
                        <?php endif; ?>

                        <td class="p-3">
                            <?= htmlspecialchars($row["leave_type"]) ?>
                        </td>

                        <td class="p-3">
                            <?= htmlspecialchars($row["start_date"]) ?>
                        </td>

                        <td class="p-3">
                            <?= htmlspecialchars($row["end_date"]) ?>
                        </td>

                        <td class="p-3">
                            <?= htmlspecialchars($row["reason"] ?? "—") ?>
                        </td>

                        <td class="p-3">
                            <?= htmlspecialchars($row["status"]) ?>
                        </td>

                        <?php if ($isManager): ?>
                            <td class="p-3">

                                <?php if ($row["status"] === "Pending"): ?>

                                    <form method="POST" class="flex gap-2">

                                        <input type="hidden"
                                            name="leave_id"
                                            value="<?= (int)$row["id"] ?>">

                                        <button type="submit"
                                            name="review_leave"
                                            value="1"
                                            onclick="this.form.new_status.value='Approved'"
                                            class="bg-green-600 text-white px-3 py-2 rounded-sm">
                                            Approve
                                        </button>

                                        <input type="hidden" name="new_status" value="">

                                        <button type="submit"
                                            name="review_leave"
                                            value="1"
                                            onclick="this.form.new_status.value='Rejected'"
                                            class="bg-red-600 text-white px-3 py-2 rounded-sm">
                                            Reject
                                        </button>

                                    </form>

                                <?php else: ?>

                                    <span class="text-gray-500">
                                        Reviewed
                                    </span>

                                <?php endif; ?>

                            </td>
                        <?php endif; ?>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>
                    <td colspan="<?= $isManager ? 7 : 5 ?>"
                        class="p-5 text-center text-gray-500">
                        No leave requests found.
                    </td>
                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

    <div class="mt-6">
        <a href="Dashboard.php"
            class="bg-gray-800 hover:text-black hover:bg-white transition text-white px-5 py-3 rounded-lg">
            Back to Dashboard
        </a>
    </div>

</div>

</body>
</html>
