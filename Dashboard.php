<?php
require_once "AuthCheck.php";

checkLogin();

$name = $_SESSION["user_name"] ?? "User";
$role = $_SESSION["user_role"] ?? "";
$roleLabels = [
    "super_admin" => "Super Admin",
    "admin" => "Administrator",
    "hr_manager" => "HR Manager",
    "employee" => "Employee"
];
$roleLabel = $roleLabels[$role] ?? "Unknown Role";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . "/includes/tailwind.php"; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Employee Management System</title>
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
<div class="min-h-screen md:flex">
    <aside class="w-full md:w-64 bg-slate-950 text-white md:min-h-screen">
        <div class="border-b border-slate-800 px-6 py-6">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-cyan-500 text-xl font-black text-slate-950">N</div>
                <div>
                    <h1 class="text-lg font-bold tracking-wide">NexaTech HR</h1>
                    <p class="text-xs text-slate-400">Management portal</p>
                </div>
            </div>
        </div>
        <nav class="space-y-1 p-4 text-sm">
            <a href="Dashboard.php" class="block rounded-xl bg-cyan-500/15 px-4 py-3 font-semibold text-cyan-300">Dashboard</a>
            <?php if (in_array($role, ["admin", "hr_manager", "super_admin"], true)): ?>
                <a href="ViewEmployees.php" class="block rounded-xl px-4 py-3 text-slate-300 hover:bg-slate-800 hover:text-white">Employees</a>
                <a href="Register.php" class="block rounded-xl px-4 py-3 text-slate-300 hover:bg-slate-800 hover:text-white">Create user</a>
                <?php if (in_array($role, ["admin", "super_admin"], true)): ?>
                    <a href="ViewDepartments.php" class="block rounded-xl px-4 py-3 text-slate-300 hover:bg-slate-800 hover:text-white">Departments</a>
                <?php endif; ?>
                <a href="Attendance.php" class="block rounded-xl px-4 py-3 text-slate-300 hover:bg-slate-800 hover:text-white">Attendance</a>
                <a href="LeaveManagement.php" class="block rounded-xl px-4 py-3 text-slate-300 hover:bg-slate-800 hover:text-white">Leave management</a>
                <a href="SalaryManagement.php" class="block rounded-xl px-4 py-3 text-slate-300 hover:bg-slate-800 hover:text-white">Salary management</a>
            <?php else: ?>
                <a href="MyProfile.php" class="block rounded-xl px-4 py-3 text-slate-300 hover:bg-slate-800 hover:text-white">My profile</a>
                <a href="Attendance.php" class="block rounded-xl px-4 py-3 text-slate-300 hover:bg-slate-800 hover:text-white">My attendance</a>
                <a href="LeaveManagement.php" class="block rounded-xl px-4 py-3 text-slate-300 hover:bg-slate-800 hover:text-white">Leave management</a>
                <a href="SalaryManagement.php" class="block rounded-xl px-4 py-3 text-slate-300 hover:bg-slate-800 hover:text-white">My salary</a>
            <?php endif; ?>
            <?php if (in_array($role, ["admin", "super_admin"], true)): ?>
                <a href="includes/UserManagement.php" class="block rounded-xl px-4 py-3 text-slate-300 hover:bg-slate-800 hover:text-white">User management</a>
            <?php endif; ?>
            <a href="logout.php" class="mt-6 block rounded-xl px-4 py-3 font-semibold text-rose-300 hover:bg-rose-500/10">Sign out</a>
        </nav>
        <div class="mx-4 mb-5 rounded-xl border border-slate-800 bg-slate-900 p-4">
            <p class="text-xs uppercase tracking-wider text-slate-500">Signed in as</p>
            <p class="mt-1 truncate font-semibold text-white"><?php echo htmlspecialchars($name); ?></p>
            <p class="mt-1 text-xs text-cyan-300"><?php echo htmlspecialchars($roleLabel); ?></p>
        </div>
    </aside>

    <main class="min-w-0 flex-1">
        <header class="flex flex-col gap-3 border-b border-slate-200 bg-white px-6 py-5 sm:flex-row sm:items-center sm:justify-between lg:px-10">
            <div>
                <p class="text-sm font-medium text-cyan-700">Employee Management System</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Dashboard</h2>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-cyan-100 font-bold text-cyan-800"><?php echo strtoupper(substr($name, 0, 1)); ?></div>
                <div>
                    <p class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($name); ?></p>
                    <p class="text-xs text-slate-500"><?php echo htmlspecialchars($roleLabel); ?></p>
                </div>
            </div>
        </header>

        <section class="px-6 py-8 lg:px-10">
            <div class="overflow-hidden rounded-2xl bg-linear-to-r from-slate-900 via-slate-800 to-cyan-900 p-7 text-white shadow-lg">
                <p class="text-sm font-medium text-cyan-200">Welcome back</p>
                <h3 class="mt-2 text-3xl font-bold"><?php echo htmlspecialchars($name); ?>!</h3>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300">
                    <?php if ($role === "super_admin"): ?>
                        Full control over the whole system — manage accounts, roles, employees, departments and daily HR operations.
                    <?php elseif ($role === "admin"): ?>
                        Manage users, employees, departments and daily HR operations from one place.
                    <?php elseif ($role === "hr_manager"): ?>
                        Review employee information and coordinate day-to-day human resource operations.
                    <?php elseif ($role === "employee"): ?>
                        Welcome to your employee workspace. You can review your profile and available employee services.
                    <?php else: ?>
                        Your account role is not recognized. Please contact the system administrator.
                    <?php endif; ?>
                </p>
            </div>

            <?php if (in_array($role, ["admin", "hr_manager", "super_admin"], true)): ?>
            <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                <a href="ViewEmployees.php" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                    <div class="flex items-center justify-between"><span class="text-sm font-medium text-slate-500">Employee records</span><span class="rounded-xl bg-cyan-50 px-3 py-2 text-cyan-700">01</span></div>
                    <p class="mt-4 text-xl font-bold text-slate-900">Manage employees</p><p class="mt-1 text-sm text-slate-500">View and maintain employee records</p>
                </a>
                <?php if (in_array($role, ["admin", "super_admin"], true)): ?>
                <a href="ViewDepartments.php" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                    <div class="flex items-center justify-between"><span class="text-sm font-medium text-slate-500">Organization</span><span class="rounded-xl bg-violet-50 px-3 py-2 text-violet-700">02</span></div>
                    <p class="mt-4 text-xl font-bold text-slate-900">Departments</p><p class="mt-1 text-sm text-slate-500">View and manage departments</p>
                </a>
                <a href="includes/UserManagement.php" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                    <div class="flex items-center justify-between"><span class="text-sm font-medium text-slate-500">Access control</span><span class="rounded-xl bg-rose-50 px-3 py-2 text-rose-700">05</span></div>
                    <p class="mt-4 text-xl font-bold text-slate-900">Users &amp; roles</p><p class="mt-1 text-sm text-slate-500">Manage accounts, roles and status</p>
                </a>
                <?php endif; ?>
                <a href="Attendance.php" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                    <div class="flex items-center justify-between"><span class="text-sm font-medium text-slate-500">Daily records</span><span class="rounded-xl bg-emerald-50 px-3 py-2 text-emerald-700">03</span></div>
                    <p class="mt-4 text-xl font-bold text-slate-900">Attendance</p><p class="mt-1 text-sm text-slate-500">Open attendance management</p>
                </a>
                <a href="LeaveManagement.php" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                    <div class="flex items-center justify-between"><span class="text-sm font-medium text-slate-500">Time off</span><span class="rounded-xl bg-amber-50 px-3 py-2 text-amber-700">04</span></div>
                    <p class="mt-4 text-xl font-bold text-slate-900">Leave requests</p><p class="mt-1 text-sm text-slate-500">Review leave management</p>
                </a>
            </div>
            <?php elseif ($role === "employee"): ?>
            <div class="mt-8 grid gap-5 sm:grid-cols-2">
                <a href="MyProfile.php" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm hover:shadow-md">
                    <p class="text-sm font-medium text-slate-500">Personal workspace</p><h4 class="mt-3 text-xl font-bold">My Profile</h4><p class="mt-2 text-sm text-slate-500">View your account profile.</p>
                </a>
                <a href="LeaveManagement.php" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm hover:shadow-md">
                    <p class="text-sm font-medium text-slate-500">Employee service</p><h4 class="mt-3 text-xl font-bold">Leave Management</h4><p class="mt-2 text-sm text-slate-500">Open the leave management page.</p>
                </a>
                <a href="Attendance.php" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm hover:shadow-md">
                    <p class="text-sm font-medium text-slate-500">Daily records</p><h4 class="mt-3 text-xl font-bold">My Attendance</h4><p class="mt-2 text-sm text-slate-500">View your attendance records.</p>
                </a>
                <a href="SalaryManagement.php" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm hover:shadow-md">
                    <p class="text-sm font-medium text-slate-500">Payroll</p><h4 class="mt-3 text-xl font-bold">My Salary</h4><p class="mt-2 text-sm text-slate-500">View your salary records.</p>
                </a>
            </div>
            <?php else: ?>
                <div class="mt-8 rounded-xl border border-amber-200 bg-amber-50 p-5 text-amber-900">Your role is not recognized. Please contact the administrator.</div>
            <?php endif; ?>

            <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h4 class="font-bold text-slate-900">Quick information</h4>
                <p class="mt-2 text-sm leading-6 text-slate-600">Use the navigation panel to open the modules available to your account. Access to each module should also be checked inside its PHP page, not only hidden in the menu.</p>
            </div>
            <footer class="mt-8 text-center text-xs text-slate-400">© <?php echo date("Y"); ?> NexaTech HR · Employee Management System</footer>
        </section>
    </main>
</div>
</body>
</html>
