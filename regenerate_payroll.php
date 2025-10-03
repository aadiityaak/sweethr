<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Payroll;
use App\Services\PayrollService;

$payrollService = app(PayrollService::class);
$payroll = Payroll::find(35);

if ($payroll) {
    echo "Regenerating payroll ID: {$payroll->id}\n";
    echo "User: {$payroll->user->name}\n";
    echo "Period: {$payroll->period_name}\n\n";

    $payrollService->regeneratePayroll($payroll);

    $payroll->refresh();

    echo "✅ Payroll regenerated successfully!\n\n";
    echo "Late Minutes: {$payroll->late_minutes}\n";
    echo "Total Deductions: Rp " . number_format($payroll->total_deductions, 0, ',', '.') . "\n";
    echo "Net Salary: Rp " . number_format($payroll->net_salary, 0, ',', '.') . "\n\n";

    echo "Deductions breakdown:\n";
    foreach ($payroll->deductions as $deduction) {
        echo "- {$deduction['name']}: -Rp " . number_format($deduction['amount'], 0, ',', '.') . "\n";
    }
} else {
    echo "Payroll not found!\n";
}
