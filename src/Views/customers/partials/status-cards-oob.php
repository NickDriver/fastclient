<?php
use App\Models\Customer;
?>
<div id="status-cards" hx-swap-oob="true">
<div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5 mb-6">
    <!-- Total -->
    <a href="/customers" class="card card-status p-4">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="h-10 w-10 rounded-lg bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center">
                    <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                </div>
            </div>
            <div class="ml-3">
                <p class="text-xs font-medium text-gray-500 dark:text-warm-400">Total</p>
                <p class="text-xl font-semibold text-gray-900 dark:text-warm-100"><?= $totalCustomers ?></p>
            </div>
        </div>
    </a>

    <!-- New -->
    <a href="/customers?status=<?= Customer::STATUS_NEW ?>" class="card card-status p-4">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="h-10 w-10 rounded-lg bg-green-100 dark:bg-green-900/40 flex items-center justify-center">
                    <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="ml-3">
                <p class="text-xs font-medium text-gray-500 dark:text-warm-400">New</p>
                <p class="text-xl font-semibold text-gray-900 dark:text-warm-100"><?= $statusCounts[Customer::STATUS_NEW] ?? 0 ?></p>
            </div>
        </div>
    </a>

    <!-- Contacted -->
    <a href="/customers?status=<?= Customer::STATUS_CONTACTED ?>" class="card card-status p-4">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="h-10 w-10 rounded-lg bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center">
                    <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                    </svg>
                </div>
            </div>
            <div class="ml-3">
                <p class="text-xs font-medium text-gray-500 dark:text-warm-400">Contacted</p>
                <p class="text-xl font-semibold text-gray-900 dark:text-warm-100"><?= $statusCounts[Customer::STATUS_CONTACTED] ?? 0 ?></p>
            </div>
        </div>
    </a>

    <!-- Callback -->
    <a href="/customers?status=<?= Customer::STATUS_CALLBACK ?>" class="card card-status p-4">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="h-10 w-10 rounded-lg bg-yellow-100 dark:bg-yellow-900/40 flex items-center justify-center">
                    <svg class="h-5 w-5 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="ml-3">
                <p class="text-xs font-medium text-gray-500 dark:text-warm-400">Callback</p>
                <p class="text-xl font-semibold text-gray-900 dark:text-warm-100"><?= $statusCounts[Customer::STATUS_CALLBACK] ?? 0 ?></p>
            </div>
        </div>
    </a>

    <!-- Follow Up -->
    <a href="/customers?status=<?= Customer::STATUS_FOLLOW_UP ?>" class="card card-status p-4">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="h-10 w-10 rounded-lg bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center">
                    <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 00-3.7-3.7 48.678 48.678 0 00-7.324 0 4.006 4.006 0 00-3.7 3.7c-.017.22-.032.441-.046.662M19.5 12l3-3m-3 3l-3-3m-12 3c0 1.232.046 2.453.138 3.662a4.006 4.006 0 003.7 3.7 48.656 48.656 0 007.324 0 4.006 4.006 0 003.7-3.7c.017-.22.032-.441.046-.662M4.5 12l3 3m-3-3l-3 3" />
                    </svg>
                </div>
            </div>
            <div class="ml-3">
                <p class="text-xs font-medium text-gray-500 dark:text-warm-400">Follow Up</p>
                <p class="text-xl font-semibold text-gray-900 dark:text-warm-100"><?= $statusCounts[Customer::STATUS_FOLLOW_UP] ?? 0 ?></p>
            </div>
        </div>
    </a>
</div>
</div>
