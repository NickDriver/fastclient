<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Customer;

class CustomerController
{
    public function index(): string
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $status = $_GET['status'] ?? '';
        $search = $_GET['search'] ?? '';

        $filters = [];
        if ($status) {
            $filters['status'] = $status;
        }
        if ($search) {
            $filters['search'] = $search;
        }

        $result = Customer::all($filters, $page);

        // Check if this is an HTMX request
        if ($this->isHtmxRequest()) {
            return view('customers.partials.list', [
                'customers' => $result['data'],
                'pagination' => $result,
                'currentStatus' => $status,
                'search' => $search,
            ]);
        }

        return view('customers.index', [
            'customers' => $result['data'],
            'pagination' => $result,
            'currentStatus' => $status,
            'search' => $search,
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
            return view('customers.partials.status-badge', ['customer' => $customer]);
        }

        redirect('/customers');
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
