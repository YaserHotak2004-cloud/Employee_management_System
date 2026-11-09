<?php

require_once "DataBaseConnetoin.php";
require_once "AuthCheck.php";

checkRole(["admin", "hr_manager"]);

$message = "";

// STEP 1: Get all Departments
$departmentQuery = "SELECT * FROM departments";

$departmentResult = mysqli_query($conn, $departmentQuery);

if (!$departmentResult) {
    die("Department Query Failed: " . mysqli_error($conn));
}


// STEP 2: Check Form Submission
if (isset($_POST['submit'])) {

    // Get data from HTML Form
    $employee_code = $_POST['employee_code'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $gender = $_POST['gender'];
    $department_id = (int) $_POST['department_id'];
    $position = $_POST['position'];
    $salary = $_POST['salary'];
    $hire_date = $_POST['hire_date'];
    $status = $_POST['status'];


    // STEP 3: Check Department ID
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

    $departmentCheckResult = mysqli_stmt_get_result($checkDepartment);

    if (mysqli_num_rows($departmentCheckResult) == 0) {

        $message = "Invalid Department Selected!";

    } else {

        // STEP 4: Prepare INSERT Query
        $query = "INSERT INTO employees
        (
            employee_code,
            first_name,
            last_name,
            email,
            phone,
            gender,
            department_id,
            position,
            salary,
            hire_date,
            status
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $query);


        // STEP 5: Bind Parameters
        mysqli_stmt_bind_param(
            $stmt,
            "ssssssisdss",
            $employee_code,
            $first_name,
            $last_name,
            $email,
            $phone,
            $gender,
            $department_id,
            $position,
            $salary,
            $hire_date,
            $status
        );


        // STEP 6: Execute Query
        if (mysqli_stmt_execute($stmt)) {

            $message = "Employee Added Successfully!";

        } else {

            $message = "Error: " . mysqli_stmt_error($stmt);

        }

        mysqli_stmt_close($stmt);
    }

    mysqli_stmt_close($checkDepartment);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include __DIR__ . "/includes/tailwind.php"; ?>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Employee</title>

</head>

<body class="min-h-screen bg-slate-100">
    <div class="mx-auto max-w-4xl px-4 py-10">

        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-cyan-700">Employees</p>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Add New Employee</h1>
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

        <!-- Display Message -->
        <?php if ($message !== "") { ?>
            <div class="mb-5 rounded-lg border border-cyan-200 bg-cyan-50 px-4 py-3 text-sm font-medium text-cyan-800">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php } ?>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <!-- Employee Form -->
            <form method="POST" class="grid grid-cols-1 gap-5 md:grid-cols-2">

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Employee Code</label>
                    <input type="text" name="employee_code" required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" name="email" required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">First Name</label>
                    <input type="text" name="first_name" required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Last Name</label>
                    <input type="text" name="last_name" required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Phone</label>
                    <input type="text" name="phone"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Gender</label>
                    <select name="gender" required
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Department</label>
                    <select name="department_id" required
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                        <option value="">Select Department</option>
                        <?php while ($department = mysqli_fetch_assoc($departmentResult)) { ?>
                            <option value="<?php echo $department['id']; ?>">
                                <?php echo htmlspecialchars($department['name']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Position</label>
                    <input type="text" name="position" required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Salary</label>
                    <input type="number" name="salary" step="0.01" required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Hire Date</label>
                    <input type="date" name="hire_date" required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Status</label>
                    <select name="status" required
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <button type="submit" name="submit"
                        class="w-full rounded-lg bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-700 sm:w-auto">
                        Add Employee
                    </button>
                </div>

            </form>
        </div>

    </div>
</body>

</html>
