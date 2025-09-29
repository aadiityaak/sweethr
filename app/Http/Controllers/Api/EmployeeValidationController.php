<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class EmployeeValidationController extends Controller
{
    public function checkEmployeeId(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'employee_id' => 'required|string|max:255',
        ]);

        $exists = User::where('employee_id', $request->employee_id)->exists();

        return response()->json([
            'available' => ! $exists,
            'message' => $exists ? 'ID karyawan sudah digunakan' : 'ID karyawan tersedia',
        ]);
    }

    public function generateNextEmployeeId(): \Illuminate\Http\JsonResponse
    {
        $prefix = 'EMP';

        // Cari nomor terakhir yang digunakan dengan format EMP001, EMP002, dst
        $lastEmployee = User::where('employee_id', 'LIKE', $prefix.'%')
            ->orderByRaw('CAST(SUBSTRING(employee_id, '.(strlen($prefix) + 1).') AS UNSIGNED) DESC')
            ->first();

        if ($lastEmployee) {
            // Ambil nomor dari employee_id terakhir (EMP001 -> 001)
            $lastNumber = (int) substr($lastEmployee->employee_id, strlen($prefix));
            $nextNumber = $lastNumber + 1;
        } else {
            // Jika belum ada employee, mulai dari 1
            $nextNumber = 1;
        }

        // Format menjadi EMP001, EMP002, dst dengan padding 3 digit
        $nextEmployeeId = $prefix.str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        // Double check jika ID sudah ada (untuk safety)
        while (User::where('employee_id', $nextEmployeeId)->exists()) {
            $nextNumber++;
            $nextEmployeeId = $prefix.str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        }

        return response()->json([
            'employee_id' => $nextEmployeeId,
            'message' => 'ID karyawan berikutnya berhasil di-generate',
        ]);
    }
}
