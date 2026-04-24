<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $leaveTypes = [
            ['id' => 1, 'name' => 'Cuti Tahunan', 'description' => 'Cuti tahunan karyawan'],
            ['id' => 2, 'name' => 'Cuti Sakit', 'description' => 'Cuti karena sakit atau kesehatan'],
            ['id' => 3, 'name' => 'Cuti Pribadi', 'description' => 'Cuti untuk keperluan pribadi'],
            ['id' => 4, 'name' => 'Cuti Melahirkan', 'description' => 'Cuti melahirkan untuk ibu'],
            ['id' => 5, 'name' => 'Cuti Darurat', 'description' => 'Cuti untuk situasi darurat'],
            ['id' => 6, 'name' => 'Cuti Duka', 'description' => 'Cuti karena kedukaan keluarga'],
        ];

        foreach ($leaveTypes as $leaveType) {
            DB::table('leave_types')
                ->where('id', $leaveType['id'])
                ->update([
                    'name' => $leaveType['name'],
                    'description' => $leaveType['description'],
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $leaveTypes = [
            ['id' => 1, 'name' => 'Annual Leave', 'description' => 'Annual vacation leave'],
            ['id' => 2, 'name' => 'Sick Leave', 'description' => 'Medical/health related leave'],
            ['id' => 3, 'name' => 'Personal Leave', 'description' => 'Personal matters leave'],
            ['id' => 4, 'name' => 'Maternity Leave', 'description' => 'Maternity leave for mothers'],
            ['id' => 5, 'name' => 'Emergency Leave', 'description' => 'Emergency situations'],
            ['id' => 6, 'name' => 'Bereavement Leave', 'description' => 'Leave for family bereavement'],
        ];

        foreach ($leaveTypes as $leaveType) {
            DB::table('leave_types')
                ->where('id', $leaveType['id'])
                ->update([
                    'name' => $leaveType['name'],
                    'description' => $leaveType['description'],
                ]);
        }
    }
};
