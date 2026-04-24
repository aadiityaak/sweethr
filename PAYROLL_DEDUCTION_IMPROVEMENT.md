# Payroll Deduction System Improvement

## Overview

Perbaikan sistem perhitungan potongan gaji payroll untuk menghitung potongan secara otomatis berdasarkan data kehadiran (jumlah telat dan jumlah tidak masuk) sesuai dengan aturan yang dikonfigurasi di database.

## Problems Fixed

### 1. **Static Deduction System**
- **Before**: Potongan gaji dihitung secara manual/statis (tax, insurance, pension)
- **After**: Potongan dihitung otomatis berdasarkan deduction rules yang aktif

### 2. **No Integration with Attendance Data**
- **Before**: Potongan tidak terhubung dengan data kehadiran
- **After**: Potongan telat berdasarkan menit keterlambatan, potongan alfa berdasarkan hari tidak masuk

### 3. **Lack of Calculation Transparency**
- **Before**: User tidak melihat detail perhitungan potongan
- **After**: User dapat melihat detail perhitungan (60 menit × Rp1.000 = Rp60.000)

## Implementation Details

### 1. **Deduction Rules Configuration**
```php
// database/seeders/FixDeductionRulesSeeder.php
$deductionRules = [
    [
        'name' => 'Potongan Telat',
        'type' => 'late',
        'calculation_method' => 'per_minute',
        'amount' => 1000, // Rp 1.000 per menit
    ],
    [
        'name' => 'Potongan Tidak Masuk',
        'type' => 'absent',
        'calculation_method' => 'per_day',
        'amount' => 100000, // Rp 100.000 per hari
    ],
    // ... other rules
];
```

### 2. **Enhanced PayrollService**
```php
// app/Services/PayrollService.php
private function calculateDeductions(User $user, $salarySetting, array $attendanceData): array
{
    $deductions = [];
    $deductionRules = DeductionRule::active()->get();

    foreach ($deductionRules as $rule) {
        switch ($rule->type) {
            case 'late':
                $lateMinutes = $attendanceData['late_minutes'];
                $parameters = [
                    'minutes' => $lateMinutes,
                    'hours' => round($lateMinutes / 60, 2),
                ];
                break;
            case 'absent':
                $absentDays = $attendanceData['absent_days'];
                $parameters = ['days' => $absentDays];
                break;
            // ... other cases
        }

        $amount = $rule->calculateDeduction($salarySetting->base_salary, $parameters);

        // Store calculation details
        $deductions[] = [
            'name' => $rule->name,
            'amount' => $amount,
            'calculation_basis' => array_merge($parameters, [
                'rule_id' => $rule->id,
                'method' => $rule->calculation_method,
                'rate' => $rule->amount,
            ]),
        ];
    }

    return $deductions;
}
```

### 3. **Enhanced UI Display**
```vue
<!-- resources/js/pages/admin/Payroll/Show.vue -->
<div v-for="deduction in deductionDetails" :key="deduction.id" class="space-y-2">
    <div class="flex items-center justify-between">
        <div>
            <span class="text-gray-600">{{ deduction.name }}</span>
        </div>
        <span class="font-medium text-red-600">-{{ formatCurrency(deduction.amount) }}</span>
    </div>

    <!-- Show calculation details -->
    <div v-if="deduction.calculation_basis && (deduction.name.includes('Telat') || deduction.name.includes('Tidak Masuk'))"
         class="ml-4 rounded-lg bg-gray-50 p-2 text-xs">
        <div v-if="deduction.calculation_basis.minutes !== undefined">
            <span>Detail: {{ deduction.calculation_basis.minutes }} menit × Rp {{ formatCurrency(deduction.calculation_basis.rate || 0).replace('Rp', '').replace(/\D/g, '') }}/menit</span>
        </div>
        <div v-else-if="deduction.calculation_basis.days !== undefined">
            <span>Detail: {{ deduction.calculation_basis.days }} hari × Rp {{ formatCurrency(deduction.calculation_basis.rate || 0).replace('Rp', '').replace(/\D/g, '') }}/hari</span>
        </div>
    </div>
</div>
```

## Test Results

### Example Calculation:
```
Employee: Mohammad Naufal Rizqi
Base Salary: Rp 3.500.000
Period: November 2025

Attendance Data:
- Nov 1: 45 minutes late
- Nov 2: 15 minutes late
- Nov 3: On time
- Other days: No attendance (17 days absent)

Deduction Calculations:
✅ Potongan Telat: 60 menit × Rp1.000 = Rp60.000
✅ Potongan Tidak Masuk: 17 hari × Rp100.000 = Rp1.700.000
✅ BPJS Kesehatan: 1% × Rp3.500.000 = Rp35.000
✅ BPJS Ketenagakerjaan: 2% × Rp3.500.000 = Rp70.000
✅ Total Potongan: Rp1.865.000
✅ Gaji Bersih: Rp1.635.000
```

## Key Features

### 1. **Dynamic Calculation**
- Potongan dihitung otomatis berdasarkan data kehadiran aktual
- Support berbagai metode perhitungan (per_minute, per_day, percentage, fixed)
- Konfigurasi aturan mudah diubah melalui database

### 2. **Transparent Details**
- User dapat melihat detail perhitungan setiap potongan
- Informasi basis perhitungan yang jelas dan mudah dipahami
- Support untuk menampilkan detail dalam berbagai format

### 3. **Flexible Rules**
- Easy to add new deduction types
- Can enable/disable specific rules
- Supports complex calculation methods

## Files Modified

1. **Database Configuration**
   - `database/seeders/FixDeductionRulesSeeder.php` - New seeder for correct deduction rules

2. **Backend Logic**
   - `app/Services/PayrollService.php` - Enhanced deduction calculation logic

3. **Frontend Display**
   - `resources/js/pages/admin/Payroll/Show.vue` - Improved UI with calculation details

4. **Database Migration**
   - `database/migrations/2025_10_13_225209_add_work_shift_id_to_attendances_table.php` - Fixed duplicate column issue

## Usage

1. **Configure Deduction Rules**: Use `FixDeductionRulesSeeder` to set up proper deduction rules
2. **Generate Payroll**: Use existing payroll generation system - calculations are automatic
3. **View Details**: Check payroll detail page to see transparent calculation breakdowns

## Benefits

- ✅ **Accurate Calculations**: Based on actual attendance data
- ✅ **Transparent Process**: Users can see exactly how deductions are calculated
- ✅ **Flexible Configuration**: Easy to modify rates and rules
- ✅ **Time Saving**: Automatic calculations eliminate manual work
- ✅ **Error Reduction**: Systematic calculations reduce human error

## Future Enhancements

- Support for overtime calculations
- Integration with leave management
- Support for bonus and incentive calculations
- Payroll reporting and analytics
- Historical payroll tracking