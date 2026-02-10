<?php
use App\Models\Customer;

$title = 'Import Customers';
$importErrors = $_SESSION['import_errors'] ?? [];
unset($_SESSION['import_errors']);

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
        <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-warm-100">Import Customers</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-warm-400">Upload a CSV file to import customers in bulk.</p>
    </div>

    <!-- Import errors from previous attempt -->
    <?php if (!empty($importErrors)): ?>
        <div class="card p-4 bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-900/50">
            <h3 class="text-sm font-medium text-red-800 dark:text-red-300">Import Errors</h3>
            <ul class="mt-2 text-sm text-red-700 dark:text-red-400 list-disc list-inside space-y-1">
                <?php foreach ($importErrors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card p-6">
        <form action="/customers/import" method="POST" enctype="multipart/form-data" class="space-y-6">
            <?= csrf_field() ?>

            <div>
                <label for="csv_file" class="form-label">CSV File *</label>
                <input
                    type="file"
                    name="csv_file"
                    id="csv_file"
                    accept=".csv,text/csv"
                    required
                    class="form-input file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900/30 dark:file:text-blue-300"
                >
                <p class="mt-1 text-sm text-gray-500 dark:text-warm-400">
                    Upload a CSV file with customer data.
                </p>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-warm-700">
                <a href="/customers" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                    </svg>
                    Import Customers
                </button>
            </div>
        </form>
    </div>

    <!-- CSV Format Instructions -->
    <div class="card p-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-warm-100 mb-4">CSV Format</h2>
        <p class="text-sm text-gray-600 dark:text-warm-300 mb-4">
            Your CSV file should have the following columns. Required columns are marked with *.
        </p>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-warm-700">
                        <th class="text-left py-2 font-medium text-gray-700 dark:text-warm-200">Column</th>
                        <th class="text-left py-2 font-medium text-gray-700 dark:text-warm-200">Required</th>
                        <th class="text-left py-2 font-medium text-gray-700 dark:text-warm-200">Description</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 dark:text-warm-300">
                    <tr class="border-b border-gray-100 dark:border-warm-800">
                        <td class="py-2 font-mono">name</td>
                        <td class="py-2">Yes</td>
                        <td class="py-2">Company or customer name</td>
                    </tr>
                    <tr class="border-b border-gray-100 dark:border-warm-800">
                        <td class="py-2 font-mono">email</td>
                        <td class="py-2">Yes</td>
                        <td class="py-2">Email address</td>
                    </tr>
                    <tr class="border-b border-gray-100 dark:border-warm-800">
                        <td class="py-2 font-mono">phone</td>
                        <td class="py-2">Yes</td>
                        <td class="py-2">Phone number</td>
                    </tr>
                    <tr class="border-b border-gray-100 dark:border-warm-800">
                        <td class="py-2 font-mono">city</td>
                        <td class="py-2">Yes</td>
                        <td class="py-2">City</td>
                    </tr>
                    <tr class="border-b border-gray-100 dark:border-warm-800">
                        <td class="py-2 font-mono">state</td>
                        <td class="py-2">Yes</td>
                        <td class="py-2">State</td>
                    </tr>
                    <tr class="border-b border-gray-100 dark:border-warm-800">
                        <td class="py-2 font-mono">website</td>
                        <td class="py-2">No</td>
                        <td class="py-2">Website URL</td>
                    </tr>
                    <tr class="border-b border-gray-100 dark:border-warm-800">
                        <td class="py-2 font-mono">industry</td>
                        <td class="py-2">No</td>
                        <td class="py-2">Industry type</td>
                    </tr>
                    <tr>
                        <td class="py-2 font-mono">status</td>
                        <td class="py-2">No</td>
                        <td class="py-2">new, contacted, callback, or follow_up (defaults to "new")</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            <h3 class="text-sm font-medium text-gray-700 dark:text-warm-200 mb-2">Example CSV</h3>
            <pre class="bg-gray-100 dark:bg-warm-900 p-4 rounded-lg overflow-x-auto text-xs font-mono text-gray-700 dark:text-warm-300">name,email,phone,website,city,state,industry,status
"Acme Corp",contact@acme.com,555-0100,https://acme.com,Austin,TX,Technology,new
"Beta LLC",info@beta.com,555-0200,,Denver,CO,Finance,new</pre>
        </div>

        <div class="mt-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
                <div class="text-sm text-yellow-800 dark:text-yellow-300">
                    <strong>Duplicate Handling:</strong> If an imported customer has an email that already exists in the system, the new record will still be imported but will be flagged for review. You can then decide whether to merge or delete the duplicate.
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
