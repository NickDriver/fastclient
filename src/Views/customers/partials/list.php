<?php
use App\Models\Customer;
?>

<?php if (empty($customers)): ?>
    <div class="card p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-warm-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
        </svg>
        <h3 class="mt-4 text-sm font-medium text-gray-900 dark:text-warm-100">No customers found</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-warm-400">Get started by adding a new customer.</p>
        <div class="mt-6">
            <a href="/customers/create" class="btn btn-primary">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Add Customer
            </a>
        </div>
    </div>
<?php else: ?>
    <!-- Desktop table view -->
    <div class="hidden md:block card !overflow-visible">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-warm-700 overflow-visible">
            <thead class="bg-gray-50 dark:bg-warm-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-warm-400 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-warm-400 uppercase tracking-wider">Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-warm-400 uppercase tracking-wider">Location</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-warm-400 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-warm-800 divide-y divide-gray-200 dark:divide-warm-700">
                <?php foreach ($customers as $customer): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-warm-700 cursor-pointer" onclick="window.location.href='/customers/<?= $customer->id ?>'">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900 dark:text-warm-100"><?= e($customer->name) ?></div>
                            <?php if ($customer->website): ?>
                                <div class="text-sm text-gray-500 dark:text-warm-400"><?= e($customer->website) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-warm-100"><?= e($customer->email) ?></div>
                            <div class="text-sm text-gray-500 dark:text-warm-400"><?= e($customer->phone) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-warm-100"><?= e($customer->city) ?>, <?= e($customer->state) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap" id="status-<?= $customer->id ?>" onclick="event.stopPropagation()">
                            <?php require __DIR__ . '/status-badge.php'; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Mobile card view -->
    <div class="md:hidden space-y-4">
        <?php foreach ($customers as $customer): ?>
            <a href="/customers/<?= $customer->id ?>" class="card p-4 block">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="font-medium text-gray-900 dark:text-warm-100"><?= e($customer->name) ?></h3>
                        <?php if ($customer->website): ?>
                            <p class="text-sm text-gray-500 dark:text-warm-400"><?= e($customer->website) ?></p>
                        <?php endif; ?>
                    </div>
                    <div id="status-mobile-<?= $customer->id ?>" onclick="event.preventDefault(); event.stopPropagation()">
                        <?php require __DIR__ . '/status-badge.php'; ?>
                    </div>
                </div>
                <div class="mt-3 space-y-1 text-sm text-gray-600 dark:text-warm-300">
                    <p><?= e($customer->email) ?></p>
                    <p><?= e($customer->phone) ?></p>
                    <p><?= e($customer->city) ?>, <?= e($customer->state) ?></p>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($pagination['totalPages'] > 1): ?>
        <nav class="flex items-center justify-between px-4 py-3 bg-white dark:bg-warm-800 border border-gray-200 dark:border-warm-700 rounded-lg sm:px-6">
            <div class="hidden sm:block">
                <p class="text-sm text-gray-700 dark:text-warm-300">
                    Showing <span class="font-medium"><?= (($pagination['page'] - 1) * $pagination['perPage']) + 1 ?></span>
                    to <span class="font-medium"><?= min($pagination['page'] * $pagination['perPage'], $pagination['total']) ?></span>
                    of <span class="font-medium"><?= $pagination['total'] ?></span> results
                </p>
            </div>
            <div class="flex gap-2">
                <?php
                $baseUrl = '/customers?' . http_build_query(array_filter([
                    'status' => $currentStatus ?? '',
                    'search' => $search ?? '',
                ]));
                ?>
                <?php if ($pagination['page'] > 1): ?>
                    <a
                        href="<?= $baseUrl ?>&page=<?= $pagination['page'] - 1 ?>"
                        hx-get="<?= $baseUrl ?>&page=<?= $pagination['page'] - 1 ?>"
                        hx-target="#customer-list"
                        hx-push-url="true"
                        class="btn btn-sm btn-secondary"
                    >Previous</a>
                <?php endif; ?>
                <?php if ($pagination['page'] < $pagination['totalPages']): ?>
                    <a
                        href="<?= $baseUrl ?>&page=<?= $pagination['page'] + 1 ?>"
                        hx-get="<?= $baseUrl ?>&page=<?= $pagination['page'] + 1 ?>"
                        hx-target="#customer-list"
                        hx-push-url="true"
                        class="btn btn-sm btn-secondary"
                    >Next</a>
                <?php endif; ?>
            </div>
        </nav>
    <?php endif; ?>
<?php endif; ?>
