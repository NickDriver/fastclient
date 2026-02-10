<?php
use App\Models\Customer;
?>
<div class="relative inline-block text-left" x-data="{ open: false }">
    <button
        type="button"
        class="badge badge-<?= $customer->status ?> cursor-pointer hover:opacity-80"
        onclick="this.nextElementSibling.classList.toggle('hidden')"
    >
        <?= e($customer->getStatusLabel()) ?>
        <svg class="ml-1 h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
        </svg>
    </button>
    <div class="hidden absolute left-0 z-50 mt-1 w-36 origin-top-left rounded-md bg-white dark:bg-warm-800 shadow-lg ring-1 ring-black ring-opacity-5 dark:ring-warm-600 focus:outline-none">
        <div class="py-1">
            <?php foreach (Customer::STATUSES as $value => $label): ?>
                <button
                    hx-post="/customers/<?= $customer->id ?>/status"
                    hx-vals='{"status": "<?= $value ?>"}'
                    hx-target="#status-<?= $customer->id ?>"
                    hx-swap="innerHTML"
                    class="block w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-warm-200 hover:bg-gray-100 dark:hover:bg-warm-700 <?= $customer->status === $value ? 'bg-gray-50 dark:bg-warm-700 font-medium' : '' ?>"
                    onclick="this.closest('.relative').querySelector('div').classList.add('hidden')"
                >
                    <?= e($label) ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
</div>
