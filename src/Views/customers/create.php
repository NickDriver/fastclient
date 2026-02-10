<?php
use App\Models\Customer;

$title = 'Add Customer';
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);

ob_start();
?>

<div class="space-y-6">
    <div>
        <a href="/customers" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Back to Customers
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">Add Customer</h1>
    </div>

    <div class="card p-6">
        <form action="/customers" method="POST" class="space-y-6">
            <?= csrf_field() ?>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="name" class="form-label">Company/Customer Name *</label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="<?= old('name') ?>"
                        class="form-input <?= isset($errors['name']) ? 'border-red-500' : '' ?>"
                        required
                    >
                    <?php if (isset($errors['name'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= e($errors['name']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="sm:col-span-2">
                    <label for="website" class="form-label">Website</label>
                    <input
                        type="url"
                        name="website"
                        id="website"
                        value="<?= old('website') ?>"
                        class="form-input"
                        placeholder="https://example.com"
                    >
                </div>

                <div>
                    <label for="email" class="form-label">Email *</label>
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
                    <label for="phone" class="form-label">Phone *</label>
                    <input
                        type="tel"
                        name="phone"
                        id="phone"
                        value="<?= old('phone') ?>"
                        class="form-input <?= isset($errors['phone']) ? 'border-red-500' : '' ?>"
                        required
                    >
                    <?php if (isset($errors['phone'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= e($errors['phone']) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="city" class="form-label">City *</label>
                    <input
                        type="text"
                        name="city"
                        id="city"
                        value="<?= old('city') ?>"
                        class="form-input <?= isset($errors['city']) ? 'border-red-500' : '' ?>"
                        required
                    >
                    <?php if (isset($errors['city'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= e($errors['city']) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="state" class="form-label">State *</label>
                    <input
                        type="text"
                        name="state"
                        id="state"
                        value="<?= old('state') ?>"
                        class="form-input <?= isset($errors['state']) ? 'border-red-500' : '' ?>"
                        required
                    >
                    <?php if (isset($errors['state'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= e($errors['state']) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-input">
                        <?php foreach (Customer::STATUSES as $value => $label): ?>
                            <option value="<?= $value ?>" <?= old('status', Customer::STATUS_NEW) === $value ? 'selected' : '' ?>>
                                <?= e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
                <a href="/customers" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Customer</button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
unset($_SESSION['old']);
require __DIR__ . '/../layouts/app.php';
