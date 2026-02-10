<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Customer;

class DashboardController
{
    public function index(): string
    {
        $statusCounts = Customer::countByStatus();
        $totalCustomers = Customer::totalCount();

        return view('dashboard.index', [
            'totalCustomers' => $totalCustomers,
            'statusCounts' => $statusCounts,
        ]);
    }
}
