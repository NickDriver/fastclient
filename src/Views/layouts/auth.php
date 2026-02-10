<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Login') ?> - FastClient CRM</title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="h-full">
    <div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <h1 class="text-center text-3xl font-bold tracking-tight text-gray-900">
                FastClient
            </h1>
            <h2 class="mt-2 text-center text-lg text-gray-600">
                <?= e($title ?? 'Sign in to your account') ?>
            </h2>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow-lg sm:rounded-xl sm:px-10">
                <!-- Flash messages -->
                <?php if ($success = flash('success')): ?>
                    <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800">
                        <?= e($success) ?>
                    </div>
                <?php endif; ?>

                <?php if ($error = flash('error')): ?>
                    <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-800">
                        <?= e($error) ?>
                    </div>
                <?php endif; ?>

                <?= $content ?>
            </div>
        </div>
    </div>
</body>
</html>
