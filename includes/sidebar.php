<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['user_role'] ?? '';

// Works whether this partial is included from the app root or from /includes/.
$base = basename(dirname($_SERVER['PHP_SELF'])) === 'includes' ? '../' : '';
?>
<aside class="w-full md:w-64 md:min-h-screen bg-slate-950 text-white p-5">
    <h2 class="text-2xl font-bold text-cyan-400 mb-8">NexaTech HR</h2>
    <nav class="space-y-2 text-sm">
        <a href="<?php echo $base; ?>Dashboard.php" class="block p-3 rounded-lg hover:bg-slate-800 <?php echo $currentPage === 'Dashboard.php' ? 'bg-slate-800 text-cyan-300' : 'text-slate-300'; ?>">Dashboard</a>
        <?php if (in_array($role, ['admin', 'hr_manager', 'super_admin'], true)): ?>
            <a href="<?php echo $base; ?>ViewEmployees.php" class="block p-3 rounded-lg text-slate-300 hover:bg-slate-800">Employees</a>
            <a href="<?php echo $base; ?>Register.php" class="block p-3 rounded-lg text-slate-300 hover:bg-slate-800">Create user</a>
            <?php if (in_array($role, ['admin', 'super_admin'], true)): ?>
                <a href="<?php echo $base; ?>ViewDepartments.php" class="block p-3 rounded-lg text-slate-300 hover:bg-slate-800">Departments</a>
            <?php endif; ?>
            <a href="<?php echo $base; ?>Attendance.php" class="block p-3 rounded-lg text-slate-300 hover:bg-slate-800">Attendance</a>
            <a href="<?php echo $base; ?>LeaveManagement.php" class="block p-3 rounded-lg text-slate-300 hover:bg-slate-800">Leave Management</a>
            <a href="<?php echo $base; ?>SalaryManagement.php" class="block p-3 rounded-lg text-slate-300 hover:bg-slate-800">Salary Management</a>
        <?php else: ?>
            <a href="<?php echo $base; ?>MyProfile.php" class="block p-3 rounded-lg text-slate-300 hover:bg-slate-800">My Profile</a>
            <a href="<?php echo $base; ?>Attendance.php" class="block p-3 rounded-lg text-slate-300 hover:bg-slate-800">My Attendance</a>
            <a href="<?php echo $base; ?>LeaveManagement.php" class="block p-3 rounded-lg text-slate-300 hover:bg-slate-800">Leave Management</a>
            <a href="<?php echo $base; ?>SalaryManagement.php" class="block p-3 rounded-lg text-slate-300 hover:bg-slate-800">My Salary</a>
        <?php endif; ?>
        <?php if (in_array($role, ['admin', 'super_admin'], true)): ?>
            <a href="<?php echo $base; ?>includes/UserManagement.php" class="block p-3 rounded-lg text-slate-300 hover:bg-slate-800">User Management</a>
        <?php endif; ?>
        <a href="<?php echo $base; ?>logout.php" class="block p-3 mt-8 rounded-lg bg-rose-600 hover:bg-rose-700">Logout</a>
    </nav>
</aside>
