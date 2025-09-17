<?php

namespace App\Exports;

use App\Models\Attendance;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, ShouldAutoSize
{
    protected $filters;
    protected $date;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
        $this->date = $filters['date'] ?? Carbon::today()->format('Y-m-d');
    }

    public function collection()
    {
        // Query attendance records with filters (same logic as controller)
        $query = Attendance::with([
            'user' => function ($query) {
                $query->with(['department:id,name', 'position:id,title']);
            },
            'officeLocation:id,name,address',
        ])
            ->where('date', $this->date)
            ->when($this->filters['status'] ?? false, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($this->filters['department'] ?? false, function ($query, $department) {
                $query->whereHas('user', function ($q) use ($department) {
                    $q->where('department_id', $department);
                });
            })
            ->when($this->filters['office_location'] ?? false, function ($query, $officeLocation) {
                $query->where('office_location_id', $officeLocation);
            })
            ->when($this->filters['search'] ?? false, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('employee_id', 'like', "%{$search}%");
                });
            });

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'ID Karyawan',
            'Nama Karyawan',
            'Departemen',
            'Posisi',
            'Tanggal',
            'Jam Masuk',
            'Jam Keluar',
            'Durasi Kerja (Jam)',
            'Status',
            'Lokasi Kantor',
            'Lembur (Jam)',
            'Face Confidence',
            'Catatan',
        ];
    }

    public function map($attendance): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        return [
            $rowNumber,
            $attendance->user->employee_id ?? 'N/A',
            $attendance->user->name ?? 'N/A',
            $attendance->user->department->name ?? 'N/A',
            $attendance->user->position->title ?? 'N/A',
            $attendance->date->format('d/m/Y'),
            $attendance->check_in_time ? Carbon::parse($attendance->check_in_time)->format('H:i:s') : 'N/A',
            $attendance->check_out_time ? Carbon::parse($attendance->check_out_time)->format('H:i:s') : 'N/A',
            $attendance->work_duration ? number_format($attendance->work_duration / 60, 2) : '0.00',
            $this->getStatusLabel($attendance->status),
            $attendance->officeLocation->name ?? 'N/A',
            $attendance->overtime_duration ? number_format($attendance->overtime_duration / 60, 2) : '0.00',
            $attendance->face_confidence ? $attendance->face_confidence . '%' : 'N/A',
            $attendance->notes ?? '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        return [
            // Header styling
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => '4F46E5'], // Indigo color
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            // Data rows styling
            "A2:N{$lastRow}" => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E5E7EB'], // Gray-200
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            // Center align specific columns
            "A:A" => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // No
            "B:B" => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // ID Karyawan
            "F:F" => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // Tanggal
            "G:G" => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // Jam Masuk
            "H:H" => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // Jam Keluar
            "I:I" => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // Durasi Kerja
            "J:J" => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // Status
            "L:L" => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // Lembur
            "M:M" => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // Face Confidence
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 12,  // ID Karyawan
            'C' => 25,  // Nama Karyawan
            'D' => 20,  // Departemen
            'E' => 20,  // Posisi
            'F' => 12,  // Tanggal
            'G' => 12,  // Jam Masuk
            'H' => 12,  // Jam Keluar
            'I' => 15,  // Durasi Kerja
            'J' => 12,  // Status
            'K' => 20,  // Lokasi Kantor
            'L' => 12,  // Lembur
            'M' => 15,  // Face Confidence
            'N' => 30,  // Catatan
        ];
    }

    public function title(): string
    {
        $dateFormatted = Carbon::parse($this->date)->format('d-m-Y');
        return "Laporan Kehadiran {$dateFormatted}";
    }

    private function getStatusLabel($status): string
    {
        return match ($status) {
            'present' => 'Hadir',
            'late' => 'Terlambat',
            'absent' => 'Tidak Hadir',
            'sick' => 'Sakit',
            'leave' => 'Cuti',
            'holiday' => 'Libur',
            default => ucfirst($status),
        };
    }
}