<?php
use App\Models\Customer;

$sort = $sort ?? 'created_at';
$direction = $direction ?? 'desc';

// Build base URL for sort links
$sortBaseParams = array_filter([
    'status' => $currentStatus ?? '',
    'search' => $search ?? '',
]);

function sortUrl(string $column, string $currentSort, string $currentDirection, array $baseParams): string {
    $newDirection = ($currentSort === $column && $currentDirection === 'asc') ? 'desc' : 'asc';
    $params = array_merge($baseParams, ['sort' => $column, 'direction' => $newDirection]);
    return '/customers?' . http_build_query($params);
}

function sortIcon(string $column, string $currentSort, string $currentDirection): string {
    if ($currentSort !== $column) {
        return '<svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" /></svg>';
    }
    if ($currentDirection === 'asc') {
        return '<svg class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" /></svg>';
    }
    return '<svg class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>';
}
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-warm-400 uppercase tracking-wider">
                        <a href="<?= sortUrl('name', $sort, $direction, $sortBaseParams) ?>"
                           hx-get="<?= sortUrl('name', $sort, $direction, $sortBaseParams) ?>"
                           hx-target="#customer-list"
                           hx-push-url="true"
                           class="flex items-center gap-1 hover:text-gray-700 dark:hover:text-warm-200">
                            Customer
                            <?= sortIcon('name', $sort, $direction) ?>
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-warm-400 uppercase tracking-wider">Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-warm-400 uppercase tracking-wider">
                        <a href="<?= sortUrl('city', $sort, $direction, $sortBaseParams) ?>"
                           hx-get="<?= sortUrl('city', $sort, $direction, $sortBaseParams) ?>"
                           hx-target="#customer-list"
                           hx-push-url="true"
                           class="flex items-center gap-1 hover:text-gray-700 dark:hover:text-warm-200">
                            Location
                            <?= sortIcon('city', $sort, $direction) ?>
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-warm-400 uppercase tracking-wider">
                        <a href="<?= sortUrl('industry', $sort, $direction, $sortBaseParams) ?>"
                           hx-get="<?= sortUrl('industry', $sort, $direction, $sortBaseParams) ?>"
                           hx-target="#customer-list"
                           hx-push-url="true"
                           class="flex items-center gap-1 hover:text-gray-700 dark:hover:text-warm-200">
                            Industry
                            <?= sortIcon('industry', $sort, $direction) ?>
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-warm-400 uppercase tracking-wider">
                        <a href="<?= sortUrl('status', $sort, $direction, $sortBaseParams) ?>"
                           hx-get="<?= sortUrl('status', $sort, $direction, $sortBaseParams) ?>"
                           hx-target="#customer-list"
                           hx-push-url="true"
                           class="flex items-center gap-1 hover:text-gray-700 dark:hover:text-warm-200">
                            Status
                            <?= sortIcon('status', $sort, $direction) ?>
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-warm-400 uppercase tracking-wider">
                        <a href="<?= sortUrl('created_at', $sort, $direction, $sortBaseParams) ?>"
                           hx-get="<?= sortUrl('created_at', $sort, $direction, $sortBaseParams) ?>"
                           hx-target="#customer-list"
                           hx-push-url="true"
                           class="flex items-center gap-1 hover:text-gray-700 dark:hover:text-warm-200">
                            Added
                            <?= sortIcon('created_at', $sort, $direction) ?>
                        </a>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-warm-800 divide-y divide-gray-200 dark:divide-warm-700">
                <?php foreach ($customers as $customer): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-warm-700 cursor-pointer <?= $customer->needsReview ? 'bg-yellow-50 dark:bg-yellow-900/10' : '' ?>"
                        hx-get="/customers/<?= $customer->id ?>"
                        hx-push-url="true"
                        hx-target="body"
                        preload="mousedown">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <div class="font-medium text-gray-900 dark:text-warm-100"><?= e($customer->name) ?></div>
                                <?php if ($customer->needsReview): ?>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300" title="<?= e($customer->reviewReason ?? 'Needs review') ?>">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                        </svg>
                                    </span>
                                <?php endif; ?>
                            </div>
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
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-warm-100"><?= e($customer->industry ?? '-') ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap" id="status-<?= $customer->id ?>" onclick="event.stopPropagation()">
                            <?php require __DIR__ . '/status-badge.php'; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500 dark:text-warm-400"><?= date('M j, Y', strtotime($customer->created_at)) ?></div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Mobile card view -->
    <div class="md:hidden space-y-4">
        <?php foreach ($customers as $customer): ?>
            <a href="/customers/<?= $customer->id ?>"
               hx-get="/customers/<?= $customer->id ?>"
               hx-push-url="true"
               hx-target="body"
               preload="mousedown"
               class="card p-4 block <?= $customer->needsReview ? 'border-yellow-300 dark:border-yellow-700' : '' ?>">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-medium text-gray-900 dark:text-warm-100"><?= e($customer->name) ?></h3>
                            <?php if ($customer->needsReview): ?>
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                    </svg>
                                </span>
                            <?php endif; ?>
                        </div>
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
                    <?php if ($customer->industry): ?>
                        <p class="text-gray-500 dark:text-warm-400"><?= e($customer->industry) ?></p>
                    <?php endif; ?>
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
                $paginationParams = array_filter([
                    'status' => $currentStatus ?? '',
                    'search' => $search ?? '',
                    'sort' => $sort ?? 'created_at',
                    'direction' => $direction ?? 'desc',
                ]);
                $baseUrl = '/customers?' . http_build_query($paginationParams);
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
