<?php

require_once "DataBaseConnetoin.php";
require_once "AuthCheck.php";

checkRole(["admin", "hr_manager"]);
$message = "";
$employee = null;


// STEP 1: Get all Departments
$departmentQuery = "SELECT id, name FROM departments ORDER BY name";
$departmentResult = mysqli_query($conn, $departmentQuery);

if (!$departmentResult) {
    die("Department Query Failed: " . mysqli_error($conn));
}


// STEP 2: Update Employee
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id = (int) $_POST["id"];

    $employee_code = trim($_POST["employee_code"]);
    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);
    $position = trim($_POST["position"]);
    $salary = (float) $_POST["salary"];
    $hire_date = $_POST["hire_date"];
    $department_id = (int) $_POST["department_id"];


    // Check whether selected Department exists
    $checkDepartment = mysqli_prepare(
        $conn,
        "SELECT id FROM departments WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $checkDepartment,
        "i",
        $department_id
    );

    mysqli_stmt_execute($checkDepartment);
    mysqli_stmt_store_result($checkDepartment);


    if (mysqli_stmt_num_rows($checkDepartment) == 0) {

        $message = "Invalid Department Selected!";

    } else {

        // Prepare UPDATE Query
        $sql = "UPDATE employees SET
                employee_code = ?,
                first_name = ?,
                last_name = ?,
                position = ?,
                salary = ?,
                hire_date = ?,
                department_id = ?
                WHERE id = ?";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "ssssdsii",
            $employee_code,
            $first_name,
            $last_name,
            $position,
            $salary,
            $hire_date,
            $department_id,
            $id
        );


        // Execute UPDATE
        if (mysqli_stmt_execute($stmt)) {

            header("Location: ViewEmployees.php");
            exit;

        } else {

            $message = "Update Error: " . mysqli_stmt_error($stmt);

        }

        mysqli_stmt_close($stmt);
    }

    mysqli_stmt_close($checkDepartment);


    // Keep submitted values visible if an error occurs
    $employee = $_POST;
}


// STEP 3: Get Employee Information (when page first opens)
if ($employee === null && isset($_GET["id"])) {

    $id = (int) $_GET["id"];

    $sql = "SELECT * FROM employees WHERE id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $id);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {

        $employee = mysqli_fetch_assoc($result);

    } else {

        die("Employee not found.");

    }

    mysqli_stmt_close($stmt);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include __DIR__ . "/includes/tailwind.php"; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employee</title>
</head>

<body class="min-h-screen bg-slate-100">
    <div class="mx-auto max-w-4xl px-4 py-10">

        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-cyan-700">Employees</p>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Edit Employee</h1>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="Dashboard.php"
                    class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Back to Dashboard
                </a>
                <a href="ViewEmployees.php"
                    class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Back to Employees
                </a>
            </div>
        </div>

        <?php if ($message !== "") { ?>
            <div class="mb-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php } ?>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <?php if ($employee !== null) { ?>

                <form method="POST" action="EditEmployee.php" class="grid grid-cols-1 gap-5 md:grid-cols-2">

                    <input
                        type="hidden"
                        name="id"
                        value="<?php echo (int) $employee['id']; ?>"
                    >

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Employee Code</label>
                        <input
                            type="text"
                            name="employee_code"
                            value="<?php echo htmlspecialchars($employee['employee_code']); ?>"
                            required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200"
                        >
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Position</label>
                        <input
                            type="text"
                            name="position"
                            value="<?php echo htmlspecialchars($employee['position']); ?>"
                            required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200"
                        >
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">First Name</label>
                        <input
                            type="text"
                            name="first_name"
                            value="<?php echo htmlspecialchars($employee['first_name']); ?>"
                            required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200"
                        >
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Last Name</label>
                        <input
                            type="text"
                            name="last_name"
                            value="<?php echo htmlspecialchars($employee['last_name']); ?>"
                            required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200"
                        >
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Salary</label>
                        <input
                            type="number"
                            name="salary"
                            min="0"
                            step="0.01"
                            value="<?php echo htmlspecialchars($employee['salary']); ?>"
                            required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200"
                        >
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Hire Date</label>
                        <input
                            type="date"
                            name="hire_date"
                            value="<?php echo htmlspecialchars($employee['hire_date']); ?>"
                            required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200"
                        >
                    </div>

                    <!-- Department Dropdown -->
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-slate-700">Department</label>
                        <select name="department_id" required
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                            <option value="">Select Department</option>
                            <?php while ($department = mysqli_fetch_assoc($departmentResult)) { ?>
                                <option
                                    value="<?php echo (int) $department['id']; ?>"
                                    <?php
                                    if (
                                        (int) $department['id'] ===
                                        (int) $employee['department_id']
                                    ) {
                                        echo "selected";
                                    }
                                    ?>
                                >
                                    <?php echo htmlspecialchars($department['name']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <button type="submit"
                            class="w-full rounded-lg bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-700 sm:w-auto">
                            Update Employee
                        </button>
                    </div>

                </form>

            <?php } else { ?>

                <p class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                    Please open this page from the employee list.
                </p>

            <?php } ?>

        </div>

    </div>
</body>

</html>
