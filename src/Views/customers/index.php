<?php
use App\Models\Customer;

$title = 'Customers';
ob_start();
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-warm-100">Customers</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-warm-400"><?= $pagination['total'] ?> total customers</p>
        </div>
        <a href="/customers/create" class="btn btn-primary">
            <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Add Customer
        </a>
    </div>

    <!-- Filters -->
    <div class="card p-4">
        <form
            hx-get="/customers"
            hx-target="#customer-list"
            hx-trigger="submit, change from:select"
            hx-push-url="true"
            class="flex flex-col sm:flex-row gap-4"
        >
            <div class="flex-1">
                <input
                    type="search"
                    name="search"
                    placeholder="Search customers..."
                    value="<?= e($search) ?>"
                    class="form-input"
                    hx-get="/customers"
                    hx-target="#customer-list"
                    hx-trigger="keyup changed delay:300ms"
                    hx-push-url="true"
                    hx-include="[name='status']"
                >
            </div>
            <div class="sm:w-48">
                <select name="status" class="form-input">
                    <option value="">All Statuses</option>
                    <?php foreach (Customer::STATUSES as $value => $label): ?>
                        <option value="<?= $value ?>" <?= $currentStatus === $value ? 'selected' : '' ?>>
                            <?= e($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>

    <!-- Customer list -->
    <div id="customer-list" hx-trigger="customerDeleted from:body" hx-get="/customers<?= $currentStatus ? '?status=' . e($currentStatus) : '' ?>">
        <?php require __DIR__ . '/partials/list.php'; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
