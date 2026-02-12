<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50 dark:bg-warm-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Login') ?> - FastClient CRM</title>
    <link rel="stylesheet" href="/assets/css/app.css">
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
</head>
<body class="h-full dark:bg-warm-950">
    <div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <h1 class="text-center text-3xl font-bold tracking-tight text-gray-900 dark:text-warm-100">
                FastClient
            </h1>
            <h2 class="mt-2 text-center text-lg text-gray-600 dark:text-warm-400">
                <?= e($title ?? 'Sign in to your account') ?>
            </h2>
        </div>

        <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-md">
            <p class="mb-4 px-4 sm:px-0 text-center text-sm italic text-amber-700 dark:text-amber-400">
                Heads up — FastClient is currently in early beta. You're welcome to explore,
                but please be aware that stored data may be wiped as we continue development.
                Use at your own discretion.
            </p>
            <div class="bg-white dark:bg-warm-800 py-8 px-4 shadow-lg sm:rounded-xl sm:px-10">
                <!-- Flash messages -->
                <?php if ($success = flash('success')): ?>
                    <div class="mb-4 rounded-lg bg-green-50 dark:bg-green-900/30 p-4 text-sm text-green-800 dark:text-green-300">
                        <?= e($success) ?>
                    </div>
                <?php endif; ?>

                <?php if ($error = flash('error')): ?>
                    <div class="mb-4 rounded-lg bg-red-50 dark:bg-red-900/30 p-4 text-sm text-red-800 dark:text-red-300">
                        <?= e($error) ?>
                    </div>
                <?php endif; ?>

                <?= $content ?>
            </div>
        </div>
    </div>
</body>
</html>
