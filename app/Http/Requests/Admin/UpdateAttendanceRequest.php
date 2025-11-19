<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->is_admin;
    }

    public function rules(): array
    {
        $attendance = $this->route('attendance');

        return [
            'date' => [
                'required',
                'date',
                Rule::unique('attendances')
                    ->where('user_id', $attendance->user_id)
                    ->ignore($attendance->id)
            ],
            'check_in_time' => ['nullable', 'date_format:H:i'],
            'check_out_time' => ['nullable', 'date_format:H:i', 'after:check_in_time'],
            'status' => ['required', Rule::in(['present', 'late', 'absent', 'half_day'])],
            'office_location_id' => ['required', 'exists:office_locations,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'date.required' => 'Tanggal wajib diisi.',
            'date.date' => 'Format tanggal tidak valid.',
            'date.unique' => 'Karyawan sudah memiliki data kehadiran untuk tanggal yang dipilih.',
            'check_in_time.date_format' => 'Format waktu check in harus HH:MM.',
            'check_out_time.date_format' => 'Format waktu check out harus HH:MM.',
            'check_out_time.after' => 'Waktu check out harus setelah check in.',
            'status.required' => 'Status kehadiran wajib diisi.',
            'status.in' => 'Status kehadiran tidak valid.',
            'office_location_id.required' => 'Lokasi kantor wajib diisi.',
            'office_location_id.exists' => 'Lokasi kantor tidak ditemukan.',
            'notes.max' => 'Catatan maksimal 500 karakter.',
        ];
    }
}
