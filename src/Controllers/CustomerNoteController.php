<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Customer;
use App\Models\CustomerNote;

class CustomerNoteController
{
    public function store(string $customerId): string
    {
        $customer = Customer::find($customerId);

        if (!$customer) {
            http_response_code(404);
            return '{"error": "Customer not found"}';
        }

        if (!verify_csrf()) {
            http_response_code(403);
            return '{"error": "Invalid request"}';
        }

        $content = trim($_POST['content'] ?? '');

        if (empty($content)) {
            http_response_code(400);
            return '{"error": "Note content is required"}';
        }

        $note = CustomerNote::create([
            'customer_id' => $customerId,
            'content' => $content,
        ]);

        if ($this->isHtmxRequest()) {
            $notes = CustomerNote::findByCustomerId($customerId);
            return view('customers.partials.notes', ['customer' => $customer, 'notes' => $notes]);
        }

        redirect("/customers/{$customerId}");
    }

    public function update(string $customerId, string $noteId): string
    {
        $customer = Customer::find($customerId);
        $note = CustomerNote::find($noteId);

        if (!$customer || !$note || $note->customerId !== $customerId) {
            http_response_code(404);
            return '{"error": "Note not found"}';
        }

        if (!verify_csrf()) {
            http_response_code(403);
            return '{"error": "Invalid request"}';
        }

        $content = trim($_POST['content'] ?? '');

        if (empty($content)) {
            http_response_code(400);
            return '{"error": "Note content is required"}';
        }

        $note->update(['content' => $content]);

        if ($this->isHtmxRequest()) {
            $notes = CustomerNote::findByCustomerId($customerId);
            return view('customers.partials.notes', ['customer' => $customer, 'notes' => $notes]);
        }

        redirect("/customers/{$customerId}");
    }

    public function destroy(string $customerId, string $noteId): string
    {
        $customer = Customer::find($customerId);
        $note = CustomerNote::find($noteId);

        if (!$customer || !$note || $note->customerId !== $customerId) {
            http_response_code(404);
            return '{"error": "Note not found"}';
        }

        if (!verify_csrf()) {
            http_response_code(403);
            return '{"error": "Invalid request"}';
        }

        $note->delete();

        if ($this->isHtmxRequest()) {
            $notes = CustomerNote::findByCustomerId($customerId);
            return view('customers.partials.notes', ['customer' => $customer, 'notes' => $notes]);
        }

        redirect("/customers/{$customerId}");
    }

    private function isHtmxRequest(): bool
    {
        return isset($_SERVER['HTTP_HX_REQUEST']);
    }
}
