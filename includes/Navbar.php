<header class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between bg-white border-b border-slate-200 px-6 py-4 shadow-sm">
    <div>
        <h2 class="text-xl font-bold text-slate-800">NexaTech HR</h2>
        <p class="text-sm text-slate-500">Employee Management System</p>
    </div>
    <div class="text-left sm:text-right">
        <p class="font-semibold text-slate-800"><?php 
        echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); 
        ?></p>
        <span class="text-sm text-cyan-700"><?php
         echo htmlspecialchars($_SESSION['user_role'] ?? '');
          ?>
          </span>
    </div>
</header>
