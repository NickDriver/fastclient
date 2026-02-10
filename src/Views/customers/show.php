<?php
use App\Models\Customer;
use App\Models\CustomerNote;

$title = $customer->name;
$notes = CustomerNote::findByCustomerId($customer->id);
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
        <div class="mt-2 flex items-center gap-3">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-warm-100"><?= e($customer->name) ?></h1>
            <span class="badge badge-<?= $customer->status ?>"><?= e($customer->getStatusLabel()) ?></span>
            <?php if ($customer->needsReview): ?>
                <span class="badge bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300" title="<?= e($customer->reviewReason ?? 'Needs review') ?>">
                    <svg class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                    Needs Review
                </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Customer details card -->
    <div class="card p-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-warm-100 mb-4">Customer Details</h2>
        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <?php if ($customer->website): ?>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-warm-400">Website</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-warm-100">
                    <a href="<?= e($customer->website) ?>" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                        <?= e($customer->website) ?>
                    </a>
                </dd>
            </div>
            <?php endif; ?>

            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-warm-400">Email</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-warm-100">
                    <a href="mailto:<?= e($customer->email) ?>" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                        <?= e($customer->email) ?>
                    </a>
                </dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-warm-400">Phone</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-warm-100">
                    <a href="tel:<?= e($customer->phone) ?>" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                        <?= e($customer->phone) ?>
                    </a>
                </dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-warm-400">Location</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-warm-100"><?= e($customer->city) ?>, <?= e($customer->state) ?></dd>
            </div>

            <?php if ($customer->industry): ?>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-warm-400">Industry</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-warm-100"><?= e($customer->industry) ?></dd>
            </div>
            <?php endif; ?>

            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-warm-400">Created</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-warm-100"><?= date('M j, Y g:i A', strtotime($customer->created_at)) ?></dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-warm-400">Last Updated</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-warm-100"><?= date('M j, Y g:i A', strtotime($customer->updated_at)) ?></dd>
            </div>
        </dl>

        <div class="mt-6 pt-4 border-t border-gray-200 dark:border-warm-700 flex gap-3">
            <a href="/customers/<?= $customer->id ?>/edit" class="btn btn-primary">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                </svg>
                Edit Customer
            </a>
            <form action="/customers/<?= $customer->id ?>" method="POST" class="inline">
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="DELETE">
                <button
                    type="submit"
                    class="btn btn-danger"
                    onclick="return confirm('Are you sure you want to delete this customer? This action cannot be undone.')"
                >
                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg>
                    Delete Customer
                </button>
            </form>
        </div>
    </div>

    <!-- Notes Section -->
    <div class="card p-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-warm-100 mb-4">
            <svg class="h-5 w-5 inline-block mr-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
            </svg>
            Notes
        </h2>
        <div id="customer-notes">
            <?php require __DIR__ . '/partials/notes.php'; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
