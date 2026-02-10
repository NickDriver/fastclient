<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'FastClient') ?> - FastClient CRM</title>
    <link rel="stylesheet" href="/assets/css/app.css">
    <script src="https://unpkg.com/htmx.org@2.0.4"></script>
    <meta name="csrf-token" content="<?= csrf_token() ?>">
</head>
<body class="h-full" hx-headers='{"X-CSRF-TOKEN": "<?= csrf_token() ?>"}'>
    <div class="min-h-full flex">
        <!-- Sidebar overlay for mobile -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-gray-600 bg-opacity-75 z-20 hidden lg:hidden"></div>

        <!-- Sidebar -->
        <?php require __DIR__ . '/../components/sidebar.php'; ?>

        <!-- Main content -->
        <div class="flex-1 lg:pl-64">
            <!-- Top bar for mobile -->
            <div class="sticky top-0 z-10 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-sm lg:hidden">
                <button type="button" id="sidebar-toggle" class="-m-2.5 p-2.5 text-gray-700">
                    <span class="sr-only">Open sidebar</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
                <div class="flex-1 text-sm font-semibold text-gray-900">FastClient</div>
            </div>

            <main class="py-6 px-4 sm:px-6 lg:px-8">
                <!-- Flash messages -->
                <?php if ($success = flash('success')): ?>
                    <div data-flash class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800 transition-opacity duration-300">
                        <?= e($success) ?>
                    </div>
                <?php endif; ?>

                <?php if ($error = flash('error')): ?>
                    <div data-flash class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-800 transition-opacity duration-300">
                        <?= e($error) ?>
                    </div>
                <?php endif; ?>

                <?= $content ?>
            </main>
        </div>
    </div>

    <script src="/assets/js/app.js"></script>
</body>
</html>
