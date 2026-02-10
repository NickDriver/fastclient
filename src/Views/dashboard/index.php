<?php
$title = 'Dashboard';
$currentStatus = ''; // Dashboard shows all, no status filter
ob_start();
?>

<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-warm-100">Dashboard</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-warm-400">Welcome back, <?= e(auth()->name) ?>!</p>
    </div>

    <!-- Stats cards -->
    <?php require __DIR__ . '/../customers/partials/status-cards.php'; ?>

    <!-- Quick actions -->
    <div class="card p-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-warm-100 mb-4">Quick Actions</h2>
        <div class="flex flex-wrap gap-3">
            <a href="/customers/create" class="btn btn-primary">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Add Customer
            </a>
            <a href="/customers" class="btn btn-secondary">
                View All Customers
            </a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
