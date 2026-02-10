<?php
$title = 'Sign in to your account';
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);

ob_start();
?>

<form action="/login" method="POST" class="space-y-6">
    <?= csrf_field() ?>

    <div>
        <label for="email" class="form-label">Email address</label>
        <input
            type="email"
            name="email"
            id="email"
            value="<?= old('email') ?>"
            class="form-input <?= isset($errors['email']) ? 'border-red-500' : '' ?>"
            required
            autofocus
        >
        <?php if (isset($errors['email'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= e($errors['email']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="password" class="form-label">Password</label>
        <input
            type="password"
            name="password"
            id="password"
            class="form-input <?= isset($errors['password']) ? 'border-red-500' : '' ?>"
            required
        >
        <?php if (isset($errors['password'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= e($errors['password']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <button type="submit" class="btn btn-primary w-full">
            Sign in
        </button>
    </div>
</form>

<p class="mt-6 text-center text-sm text-gray-600">
    Don't have an account?
    <a href="/register" class="font-medium text-blue-600 hover:text-blue-500">
        Create one
    </a>
</p>

<?php
$content = ob_get_clean();
unset($_SESSION['old']);
require __DIR__ . '/../layouts/auth.php';
