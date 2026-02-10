<?php
$title = 'Create your account';
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);

ob_start();
?>

<form action="/register" method="POST" class="space-y-6">
    <?= csrf_field() ?>

    <div>
        <label for="name" class="form-label">Full name</label>
        <input
            type="text"
            name="name"
            id="name"
            value="<?= old('name') ?>"
            class="form-input <?= isset($errors['name']) ? 'border-red-500' : '' ?>"
            required
            autofocus
        >
        <?php if (isset($errors['name'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= e($errors['name']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="email" class="form-label">Email address</label>
        <input
            type="email"
            name="email"
            id="email"
            value="<?= old('email') ?>"
            class="form-input <?= isset($errors['email']) ? 'border-red-500' : '' ?>"
            required
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
            minlength="8"
        >
        <?php if (isset($errors['password'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= e($errors['password']) ?></p>
        <?php else: ?>
            <p class="mt-1 text-sm text-gray-500">Must be at least 8 characters</p>
        <?php endif; ?>
    </div>

    <div>
        <label for="password_confirmation" class="form-label">Confirm password</label>
        <input
            type="password"
            name="password_confirmation"
            id="password_confirmation"
            class="form-input <?= isset($errors['password_confirmation']) ? 'border-red-500' : '' ?>"
            required
        >
        <?php if (isset($errors['password_confirmation'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= e($errors['password_confirmation']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <button type="submit" class="btn btn-primary w-full">
            Create account
        </button>
    </div>
</form>

<p class="mt-6 text-center text-sm text-gray-600">
    Already have an account?
    <a href="/login" class="font-medium text-blue-600 hover:text-blue-500">
        Sign in
    </a>
</p>

<?php
$content = ob_get_clean();
unset($_SESSION['old']);
require __DIR__ . '/../layouts/auth.php';
