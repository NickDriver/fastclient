<?php
use App\Models\Customer;

$title = 'Add Customer';
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);

ob_start();
?>

<div class="space-y-6">
    <div>
        <a href="/customers" class="text-sm text-gray-500 hover:text-gray-700 dark:text-warm-400 dark:hover:text-warm-200 flex items-center gap-1">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Back to Customers
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-warm-100">Add Customer</h1>
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
                    <div class="flex gap-2">
                        <input
                            type="url"
                            name="website"
                            id="website"
                            value="<?= old('website') ?>"
                            class="form-input flex-1"
                            placeholder="https://example.com"
                        >
                        <button
                            type="button"
                            id="scrape-btn"
                            class="btn btn-secondary whitespace-nowrap flex items-center gap-1.5"
                            title="Auto-fill fields from website"
                        >
                            <svg id="scrape-icon" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5a17.92 17.92 0 01-8.716-2.247m0 0A8.966 8.966 0 013 12c0-1.777.515-3.434 1.404-4.832" />
                            </svg>
                            <svg id="scrape-spinner" class="h-4 w-4 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Fetch Info
                        </button>
                    </div>
                    <p id="scrape-status" class="mt-1 text-sm hidden"></p>
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
                    <label for="industry" class="form-label">Industry</label>
                    <input
                        type="text"
                        name="industry"
                        id="industry"
                        value="<?= old('industry') ?>"
                        class="form-input"
                        placeholder="e.g. Technology, Healthcare, Finance"
                    >
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

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-warm-700">
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
