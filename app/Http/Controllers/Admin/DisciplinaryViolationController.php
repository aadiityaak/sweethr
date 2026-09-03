<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DisciplinaryViolation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DisciplinaryViolationController extends Controller
{
    public function index(Request $request)
    {
        $violations = DisciplinaryViolation::query()
            ->when($request->get('category'), fn ($q, $c) => $q->where('category', $c))
            ->when($request->get('search'), fn ($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%"))
            ->orderBy('category')
            ->orderBy('code')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/Disciplinary/Violations/Index', [
            'violations' => $violations,
            'filters' => [
                'category' => $request->get('category', ''),
                'search' => $request->get('search', ''),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:disciplinary_violations,code'],
            'name' => ['required', 'string', 'max:191'],
            'category' => ['required', 'in:ringan,sedang,berat'],
            'points' => ['required', 'integer', 'min:1', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        DisciplinaryViolation::create($validated);

        return redirect()->route('admin.disciplinary-violations.index')
            ->with('success', 'Kategori pelanggaran ditambahkan.');
    }

    public function update(Request $request, DisciplinaryViolation $disciplinary_violation)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:disciplinary_violations,code,' . $disciplinary_violation->id],
            'name' => ['required', 'string', 'max:191'],
            'category' => ['required', 'in:ringan,sedang,berat'],
            'points' => ['required', 'integer', 'min:1', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $disciplinary_violation->update($validated);

        return redirect()->route('admin.disciplinary-violations.index')
            ->with('success', 'Kategori pelanggaran diperbarui.');
    }

    public function destroy(DisciplinaryViolation $disciplinary_violation)
    {
        if ($disciplinary_violation->employeeViolations()->exists()) {
            return redirect()->back()->with('error', 'Tidak bisa dihapus: sudah dipakai dalam catatan pelanggaran.');
        }

        $disciplinary_violation->delete();

        return redirect()->route('admin.disciplinary-violations.index')
            ->with('success', 'Kategori pelanggaran dihapus.');
    }
}
