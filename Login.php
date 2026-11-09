<?php
session_start();

include "DataBaseConnetoin.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $sql = "SELECT * FROM users WHERE email = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "s", $email);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 1) {

        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user["PASSWORD"])) {

            if (($user["status"] ?? "Active") === "Inactive") {
                $error = "Your account has been deactivated. Please contact an administrator.";
            } else {

                $_SESSION["user_id"] = $user["id"];
                $_SESSION["user_name"] = $user["name"];
                $_SESSION["user_email"] = $user["email"];
                $_SESSION["user_role"] = $user["role"];

                header("Location: Dashboard.php");
                exit();
            }

        } else {
            $error = "Incorrect Password!";
        }

    } else {
        $error = "User not found!";
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
    <title>Login</title>
</head>
<body class="min-h-screen bg-slate-100">
    <div class="flex min-h-screen items-center justify-center px-4 py-10">
        <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">

            <div class="mb-6 text-center">
                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-500 text-xl font-black text-slate-950">N</div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">User Login</h1>
                <p class="mt-1 text-sm text-slate-500">EMS For NexaTech </p>
            </div>

            <?php if ($error !== "") { ?>
                <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php } ?>

            <form method="POST" class="space-y-4">
                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" id="email" name="email" required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                </div>

                <div>
                    <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Password</label>
                    <input type="password" id="password" name="password" required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                </div>

                <button type="submit"
                    class="w-full rounded-lg bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-700">
                    Login
                </button>
            </form>

            <p class="mt-6 text-center text-xs text-slate-400">
                Accounts are created by an Administrator or HR Manager.
            </p>

        </div>
    </div>
</body>
</html>
