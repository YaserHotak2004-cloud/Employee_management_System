<?php

require_once "AuthCheck.php";

checkRole(["admin"]);

require_once "DataBaseConnetoin.php";

$message = "";
$department = null;


// STEP 1: Get Department ID and Information
if (isset($_GET['id'])) {

    $id = (int) $_GET['id'];

    $query = "SELECT * FROM departments WHERE id = ?";

    $stmt = mysqli_prepare($conn, $query);

    mysqli_stmt_bind_param($stmt, "i", $id);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {

        $department = mysqli_fetch_assoc($result);

    } else {

        die("Department not found!");

    }

    mysqli_stmt_close($stmt);

}


// STEP 2: Update Department
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id = (int) $_POST['id'];

    $name = trim($_POST['name']);
    $description = trim($_POST['description']);


    if ($name == "") {

        $message = "Department name is required.";

    } else {

        $query = "UPDATE departments
                  SET name = ?, description = ?
                  WHERE id = ?";

        $stmt = mysqli_prepare($conn, $query);

        mysqli_stmt_bind_param(
            $stmt,
            "ssi",
            $name,
            $description,
            $id
        );


        if (mysqli_stmt_execute($stmt)) {

            header("Location: ViewDepartments.php");
            exit();

        } else {

            $message = "Error: " . mysqli_stmt_error($stmt);

        }

        mysqli_stmt_close($stmt);


        // Keep submitted values visible if update fails
        $department = [
            "id" => $id,
            "name" => $name,
            "description" => $description
        ];

    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include __DIR__ . "/includes/tailwind.php"; ?>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Department</title>

</head>

<body class="min-h-screen bg-slate-100">
    <div class="mx-auto max-w-2xl px-4 py-10">

        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-cyan-700">Departments</p>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Edit Department</h1>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="Dashboard.php"
                    class="rounded-lg border border-slate-300 bg-gray-800 hover:text-black px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Back to Dashboard
                </a>
                <a href="ViewDepartments.php"
                    class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Back to Departments
                </a>
            </div>
        </div>

        <?php if ($message !== "") { ?>
            <div class="mb-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php } ?>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <?php if ($department !== null) { ?>

                <form method="POST" action="EditDepartment.php" class="space-y-5">

                    <input
                        type="hidden"
                        name="id"
                        value="<?php echo (int) $department['id']; ?>"
                    >

                    <div>
                        <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Department Name</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="<?php echo htmlspecialchars($department['name']); ?>"
                            required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200"
                        >
                    </div>

                    <div>
                        <label for="description" class="mb-1 block text-sm font-medium text-slate-700">Description</label>
                        <textarea
                            id="description"
                            name="description"
                            rows="4"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200"
                        ><?php echo htmlspecialchars($department['description']); ?></textarea>
                    </div>

                    <button type="submit" name="update"
                        class="w-full rounded-lg bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-700 sm:w-auto">
                        Update Department
                    </button>

                </form>

            <?php } else { ?>

                <p class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                    Department information is not available.
                </p>

            <?php } ?>

        </div>

    </div>
</body>

</html>
