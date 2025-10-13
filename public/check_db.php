<?php
require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // Check if work_shift_id column exists
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('attendances');
    echo '<h2>Columns in attendances table:</h2>';
    echo '<ul>';
    foreach ($columns as $column) {
        $exists = $column === 'work_shift_id' ? ' <strong style="color: green;">✓ EXISTS</strong>' : '';
        echo '<li>' . $column . $exists . '</li>';
    }
    echo '</ul>';

    // Check if shift ID 7 exists in work_shifts table
    echo '<h2>Checking Shift ID 7:</h2>';
    $shift = \DB::table('work_shifts')->where('id', 7)->first();
    if ($shift) {
        echo '<p style="color: green;">✓ Shift ID 7 exists: ' . $shift->name . ' (' . $shift->start_time . ' - ' . $shift->end_time . ')</p>';
    } else {
        echo '<p style="color: red;">✗ Shift ID 7 does not exist</p>';
    }

    // Check recent attendance records with shift info
    echo '<h2>Recent Attendance Records:</h2>';
    $attendances = \DB::table('attendances')
        ->leftJoin('users', 'attendances.user_id', '=', 'users.id')
        ->leftJoin('work_shifts', 'attendances.work_shift_id', '=', 'work_shifts.id')
        ->where('users.employee_id', 'EMP011')
        ->orderBy('attendances.date', 'desc')
        ->limit(5)
        ->select([
            'attendances.id',
            'attendances.date',
            'attendances.check_in_time',
            'attendances.work_shift_id',
            'work_shifts.name as shift_name',
            'work_shifts.start_time as shift_start',
            'work_shifts.end_time as shift_end'
        ])
        ->get();

    if ($attendances->count() > 0) {
        echo '<table border="1" cellpadding="5">';
        echo '<tr><th>Date</th><th>Check In</th><th>Work Shift ID</th><th>Shift Name</th><th>Shift Time</th></tr>';
        foreach ($attendances as $att) {
            echo '<tr>';
            echo '<td>' . $att->date . '</td>';
            echo '<td>' . $att->check_in_time . '</td>';
            echo '<td>' . ($att->work_shift_id ?: 'NULL') . '</td>';
            echo '<td>' . ($att->shift_name ?: 'N/A') . '</td>';
            echo '<td>' . ($att->shift_start && $att->shift_end ? $att->shift_start . ' - ' . $att->shift_end : 'N/A') . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo '<p>No attendance records found for EMP011</p>';
    }

} catch (Exception $e) {
    echo '<h2 style="color: red;">Database Error:</h2>';
    echo '<p>' . $e->getMessage() . '</p>';
}
?>