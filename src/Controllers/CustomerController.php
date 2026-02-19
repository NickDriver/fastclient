<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Customer;
use App\Services\WebScraper;

class CustomerController
{
    public function index(): string
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $status = $_GET['status'] ?? '';
        $search = $_GET['search'] ?? '';
        $sort = $_GET['sort'] ?? 'created_at';
        $direction = $_GET['direction'] ?? 'desc';

        $filters = [];
        if ($status) {
            $filters['status'] = $status;
        }
        if ($search) {
            $filters['search'] = $search;
        }

        $result = Customer::all($filters, $page, 10, $sort, $direction);

        // Check if this is an HTMX request
        if ($this->isHtmxRequest()) {
            return view('customers.partials.list', [
                'customers' => $result['data'],
                'pagination' => $result,
                'currentStatus' => $status,
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
            ]);
        }

        return view('customers.index', [
            'customers' => $result['data'],
            'pagination' => $result,
            'currentStatus' => $status,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
            'statusCounts' => Customer::countByStatus(),
            'totalCustomers' => Customer::totalCount(),
        ]);
    }

    public function create(): string
    {
        return view('customers.create');
    }

    public function store(): string
    {
        if (!verify_csrf()) {
            flash('error', 'Invalid request. Please try again.');
            redirect('/customers/create');
        }

        $data = $this->validateCustomerData();

        if (!empty($_SESSION['errors'])) {
            redirect('/customers/create');
        }

        Customer::create($data);

        flash('success', 'Customer created successfully.');
        redirect('/customers');
    }

    public function show(string $id): string
    {
        $customer = Customer::find($id);

        if (!$customer) {
            http_response_code(404);
            return view('errors.404');
        }

        // Cache for 60 seconds to enable HTMX preload
        header('Cache-Control: private, max-age=60');

        return view('customers.show', ['customer' => $customer]);
    }

    public function edit(string $id): string
    {
        $customer = Customer::find($id);

        if (!$customer) {
            http_response_code(404);
            return view('errors.404');
        }

        return view('customers.edit', ['customer' => $customer]);
    }

    public function update(string $id): string
    {
        $customer = Customer::find($id);

        if (!$customer) {
            http_response_code(404);
            return view('errors.404');
        }

        if (!verify_csrf()) {
            flash('error', 'Invalid request. Please try again.');
            redirect("/customers/{$id}/edit");
        }

        $data = $this->validateCustomerData();

        if (!empty($_SESSION['errors'])) {
            redirect("/customers/{$id}/edit");
        }

        $customer->update($data);

        flash('success', 'Customer updated successfully.');
        redirect('/customers');
    }

    public function destroy(string $id): string
    {
        $customer = Customer::find($id);

        if (!$customer) {
            http_response_code(404);
            return view('errors.404');
        }

        if (!verify_csrf()) {
            flash('error', 'Invalid request. Please try again.');
            redirect('/customers');
        }

        $customer->delete();

        if ($this->isHtmxRequest()) {
            // Return empty response with HX-Trigger to refresh the list
            header('HX-Trigger: customerDeleted');
            return '';
        }

        flash('success', 'Customer deleted successfully.');
        redirect('/customers');
    }

    public function updateStatus(string $id): string
    {
        $customer = Customer::find($id);

        if (!$customer) {
            http_response_code(404);
            return '{"error": "Customer not found"}';
        }

        $status = $_POST['status'] ?? '';

        if (!array_key_exists($status, Customer::STATUSES)) {
            http_response_code(400);
            return '{"error": "Invalid status"}';
        }

        $customer = $customer->updateStatus($status);

        if ($this->isHtmxRequest()) {
            header('HX-Trigger: statusChanged');

            // Main response: desktop badge
            $badge = view('customers.partials.status-badge', ['customer' => $customer]);

            // OOB: mobile badge
            $mobileBadge = '<div id="status-mobile-' . $customer->id . '" hx-swap-oob="innerHTML">'
                . view('customers.partials.status-badge', ['customer' => $customer])
                . '</div>';

            // OOB: status cards with updated counts
            $statusCards = view('customers.partials.status-cards-oob', [
                'statusCounts' => Customer::countByStatus(),
                'totalCustomers' => Customer::totalCount(),
            ]);

            return $badge . $mobileBadge . $statusCards;
        }

        redirect('/customers');
    }

    public function scrape(): string
    {
        header('Content-Type: application/json');

        if (!verify_csrf()) {
            http_response_code(403);
            return json_encode(['success' => false, 'error' => 'Invalid CSRF token.']);
        }

        $input = json_decode(file_get_contents('php://input') ?: '', true);
        $url = trim(is_array($input) ? ($input['url'] ?? '') : '');

        if ($url === '') {
            http_response_code(400);
            return json_encode(['success' => false, 'error' => 'Please enter a website URL first.']);
        }

        try {
            $scraper = new WebScraper();
            $result = $scraper->scrape($url);
            return json_encode($result);
        } catch (\Throwable $e) {
            http_response_code(500);
            return json_encode(['success' => false, 'error' => 'An error occurred while scraping the website.']);
        }
    }

    public function import(): string
    {
        return view('customers.import');
    }

    public function processImport(): string
    {
        if (!verify_csrf()) {
            flash('error', 'Invalid request. Please try again.');
            redirect('/customers/import');
        }

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            flash('error', 'Please upload a valid CSV file.');
            redirect('/customers/import');
        }

        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');

        if (!$handle) {
            flash('error', 'Could not read the uploaded file.');
            redirect('/customers/import');
        }

        // Read header row
        $headers = fgetcsv($handle, 0, ",", "\"", "");
        if (!$headers) {
            fclose($handle);
            flash('error', 'CSV file is empty or invalid.');
            redirect('/customers/import');
        }

        // Normalize headers (lowercase, trim)
        $headers = array_map(fn($h) => strtolower(trim($h)), $headers);

        // Required columns
        $requiredColumns = ['name', 'phone', 'city', 'state'];
        $missingColumns = array_diff($requiredColumns, $headers);

        if (!empty($missingColumns)) {
            fclose($handle);
            flash('error', 'Missing required columns: ' . implode(', ', $missingColumns));
            redirect('/customers/import');
        }

        $imported = 0;
        $errors = [];
        $rowNum = 1;

        while (($row = fgetcsv($handle, 0, ",", "\"", "")) !== false) {
            $rowNum++;

            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            // Map row to associative array
            $data = [];
            foreach ($headers as $i => $header) {
                $data[$header] = trim($row[$i] ?? '');
            }

            // Validate required fields
            $rowErrors = [];
            if (empty($data['name'])) {
                $rowErrors[] = 'name is required';
            }
            if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $rowErrors[] = 'invalid email format';
            }
            if (empty($data['phone'])) {
                $rowErrors[] = 'phone is required';
            }
            if (empty($data['city'])) {
                $rowErrors[] = 'city is required';
            }
            if (empty($data['state'])) {
                $rowErrors[] = 'state is required';
            }

            if (!empty($rowErrors)) {
                $errors[] = "Row {$rowNum}: " . implode(', ', $rowErrors);
                continue;
            }

            // Check for duplicate email
            $existingCustomer = !empty($data['email']) ? Customer::findByEmail($data['email']) : null;
            $needsReview = false;
            $reviewReason = null;

            if ($existingCustomer) {
                $needsReview = true;
                $reviewReason = 'Duplicate email: ' . $data['email'];
            }

            // Validate status if provided
            $status = $data['status'] ?? Customer::STATUS_NEW;
            if (!array_key_exists($status, Customer::STATUSES)) {
                $status = Customer::STATUS_NEW;
            }

            // Create customer
            Customer::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'website' => $data['website'] ?? '',
                'city' => $data['city'],
                'state' => $data['state'],
                'industry' => $data['industry'] ?? '',
                'status' => $status,
                'needs_review' => $needsReview,
                'review_reason' => $reviewReason,
            ]);

            $imported++;
        }

        fclose($handle);

        if ($imported > 0) {
            $message = "Successfully imported {$imported} customer(s).";
            if (!empty($errors)) {
                $message .= ' Some rows had errors.';
            }
            flash('success', $message);
        } else {
            flash('error', 'No customers were imported.');
        }

        if (!empty($errors)) {
            $_SESSION['import_errors'] = $errors;
        }

        redirect('/customers/import');
    }

    public function export(): void
    {
        $status = $_GET['status'] ?? '';
        $search = $_GET['search'] ?? '';

        $filters = [];
        if ($status) {
            $filters['status'] = $status;
        }
        if ($search) {
            $filters['search'] = $search;
        }

        // Fetch ALL customers matching current filters (no pagination)
        $customers = Customer::all($filters, 1, 10000);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="customers_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['name', 'email', 'phone', 'website', 'city', 'state', 'industry', 'status']);

        foreach ($customers['data'] as $customer) {
            fputcsv($output, [
                $customer->name,
                $customer->email,
                $customer->phone,
                $customer->website ?? '',
                $customer->city,
                $customer->state,
                $customer->industry ?? '',
                $customer->status
            ]);
        }
        fclose($output);
        exit;
    }

    private function validateCustomerData(): array
    {
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'website' => trim($_POST['website'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'state' => trim($_POST['state'] ?? ''),
            'industry' => trim($_POST['industry'] ?? ''),
            'status' => $_POST['status'] ?? Customer::STATUS_NEW,
        ];

        $errors = [];

        if (empty($data['name'])) {
            $errors['name'] = 'Name is required';
        }

        if (empty($data['phone'])) {
            $errors['phone'] = 'Phone is required';
        }

        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address';
        }

        if (empty($data['city'])) {
            $errors['city'] = 'City is required';
        }

        if (empty($data['state'])) {
            $errors['state'] = 'State is required';
        }

        if (!empty($errors)) {
            $_SESSION['old'] = $data;
            $_SESSION['errors'] = $errors;
        }

        return $data;
    }

    private function isHtmxRequest(): bool
    {
        return isset($_SERVER['HTTP_HX_REQUEST']);
    }
}
