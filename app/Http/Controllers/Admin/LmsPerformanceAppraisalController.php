<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LmsPerformanceAppraisal;
use App\Models\LmsPerformanceAppraisalParameter;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class LmsPerformanceAppraisalController extends Controller
{
    private const SCORE_KEYS = [
        'quality_work',
        'quantity_work',
        'task_knowledge',
        'discipline',
        'teamwork',
        'communication',
        'initiative',
        'target_realization',
        'time_management',
        'attitude',
        'adaptability',
        'leadership_delegation',
        'leadership_development',
    ];

    public function index(Request $request)
    {
        $parameters = $this->activeParameters();

        $appraisals = LmsPerformanceAppraisal::query()
            ->with([
                'user:id,name,employee_id',
                'evaluator:id,name',
            ])
            ->latest('evaluated_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $appraisals->setCollection(
            $appraisals->getCollection()->map(function (LmsPerformanceAppraisal $a) use ($parameters) {
                $values = [];
                $total = 0;

                foreach ($parameters as $p) {
                    $key = (string) $p['key'];
                    $val = $a->getAttribute($key);
                    if ($val === null) {
                        continue;
                    }
                    $values[] = (int) $val;
                    $total += (int) $val;
                }

                $count = count($values);
                $avg = $count > 0 ? round($total / $count, 2) : null;

                $a->setAttribute('score_total', $total);
                $a->setAttribute('score_count', $count);
                $a->setAttribute('score_avg', $avg);

                return $a;
            })
        );

        return Inertia::render('admin/Lms/PerformanceAppraisal/Index', [
            'appraisals' => $appraisals,
            'parameters' => $parameters,
        ]);
    }

    public function create()
    {
        $employees = User::query()
            ->where('is_admin', false)
            ->withCount('subordinates')
            ->orderBy('name')
            ->get(['id', 'name', 'employee_id']);

        return Inertia::render('admin/Lms/PerformanceAppraisal/Create', [
            'employees' => $employees,
            'parameters' => $this->activeParameters(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatedPayload($request);

        $validated['evaluator_id'] = auth()->id();

        LmsPerformanceAppraisal::create($validated);

        return redirect()->route('admin.lms-performance-appraisals.index')
            ->with('success', 'Performance appraisal berhasil disimpan.');
    }

    public function edit(LmsPerformanceAppraisal $lms_performance_appraisal)
    {
        $employees = User::query()
            ->where('is_admin', false)
            ->withCount('subordinates')
            ->orderBy('name')
            ->get(['id', 'name', 'employee_id']);

        $lms_performance_appraisal->load(['user:id,name,employee_id']);

        return Inertia::render('admin/Lms/PerformanceAppraisal/Edit', [
            'employees' => $employees,
            'appraisal' => $lms_performance_appraisal,
            'parameters' => $this->activeParameters(),
        ]);
    }

    public function update(Request $request, LmsPerformanceAppraisal $lms_performance_appraisal)
    {
        $validated = $this->validatedPayload($request);
        $validated['evaluator_id'] = auth()->id();

        $lms_performance_appraisal->update($validated);

        return redirect()->route('admin.lms-performance-appraisals.index')
            ->with('success', 'Performance appraisal berhasil diperbarui.');
    }

    public function destroy(LmsPerformanceAppraisal $lms_performance_appraisal)
    {
        $lms_performance_appraisal->delete();

        return redirect()->route('admin.lms-performance-appraisals.index')
            ->with('success', 'Performance appraisal berhasil dihapus.');
    }

    private function validatedPayload(Request $request): array
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'evaluated_at' => 'required|date',
            'leadership_delegation' => 'nullable|integer|min:1|max:5',
            'leadership_development' => 'nullable|integer|min:1|max:5',
            'feedback' => 'nullable|string',
        ]);

        foreach (self::SCORE_KEYS as $key) {
            if (in_array($key, ['leadership_delegation', 'leadership_development'], true)) {
                continue;
            }
            $validated[$key] = $request->has($key) ? (int) $request->input($key) : null;
            if ($validated[$key] !== null) {
                $request->validate([$key => 'integer|min:1|max:5']);
            }
        }

        $employee = User::query()->whereKey($validated['user_id'])->firstOrFail();
        $isManager = $employee->subordinates()->exists();

        $requiredKeys = collect($this->activeParameters())
            ->filter(fn ($p) => (bool) ($p['is_active'] ?? true))
            ->filter(function ($p) use ($isManager) {
                $managerOnly = (bool) ($p['managerial_only'] ?? false);
                return ! $managerOnly || $isManager;
            })
            ->pluck('key')
            ->values()
            ->all();

        foreach ($requiredKeys as $key) {
            if (! array_key_exists($key, $validated) || $validated[$key] === null) {
                throw ValidationException::withMessages([$key => 'Wajib diisi.']);
            }
        }

        if (! $isManager) {
            $validated['leadership_delegation'] = null;
            $validated['leadership_development'] = null;
        }

        foreach (self::SCORE_KEYS as $key) {
            if (in_array($key, ['leadership_delegation', 'leadership_development'], true)) {
                continue;
            }
            if (! array_key_exists($key, $validated) || $validated[$key] === null) {
                $validated[$key] = 3;
            }
        }

        return $validated;
    }

    private function activeParameters(): array
    {
        $parameters = LmsPerformanceAppraisalParameter::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['key', 'group', 'label', 'sort_order', 'is_active', 'managerial_only'])
            ->map(fn (LmsPerformanceAppraisalParameter $p) => [
                'key' => $p->key,
                'group' => $p->group,
                'label' => $p->label,
                'sort_order' => (int) $p->sort_order,
                'is_active' => (bool) $p->is_active,
                'managerial_only' => (bool) $p->managerial_only,
            ])
            ->values()
            ->all();

        if (count($parameters) > 0) {
            return $parameters;
        }

        return [
            ['key' => 'quality_work', 'group' => 'Kompetensi Teknis (Hard Skills)', 'label' => 'Kualitas Kerja', 'sort_order' => 10, 'is_active' => true, 'managerial_only' => false],
            ['key' => 'quantity_work', 'group' => 'Kompetensi Teknis (Hard Skills)', 'label' => 'Kuantitas Kerja', 'sort_order' => 20, 'is_active' => true, 'managerial_only' => false],
            ['key' => 'task_knowledge', 'group' => 'Kompetensi Teknis (Hard Skills)', 'label' => 'Pengetahuan Tugas', 'sort_order' => 30, 'is_active' => true, 'managerial_only' => false],
            ['key' => 'discipline', 'group' => 'Perilaku Kerja (Soft Skills)', 'label' => 'Kedisiplinan', 'sort_order' => 40, 'is_active' => true, 'managerial_only' => false],
            ['key' => 'teamwork', 'group' => 'Perilaku Kerja (Soft Skills)', 'label' => 'Kerja Sama Tim', 'sort_order' => 50, 'is_active' => true, 'managerial_only' => false],
            ['key' => 'communication', 'group' => 'Perilaku Kerja (Soft Skills)', 'label' => 'Komunikasi', 'sort_order' => 60, 'is_active' => true, 'managerial_only' => false],
            ['key' => 'initiative', 'group' => 'Perilaku Kerja (Soft Skills)', 'label' => 'Inisiatif', 'sort_order' => 70, 'is_active' => true, 'managerial_only' => false],
            ['key' => 'target_realization', 'group' => 'Pencapaian Target (KPI)', 'label' => 'Realisasi Target', 'sort_order' => 80, 'is_active' => true, 'managerial_only' => false],
            ['key' => 'time_management', 'group' => 'Pencapaian Target (KPI)', 'label' => 'Manajemen Waktu', 'sort_order' => 90, 'is_active' => true, 'managerial_only' => false],
            ['key' => 'attitude', 'group' => 'Sikap dan Adaptabilitas', 'label' => 'Sikap (Attitude)', 'sort_order' => 100, 'is_active' => true, 'managerial_only' => false],
            ['key' => 'adaptability', 'group' => 'Sikap dan Adaptabilitas', 'label' => 'Adaptabilitas', 'sort_order' => 110, 'is_active' => true, 'managerial_only' => false],
            ['key' => 'leadership_delegation', 'group' => 'Kepemimpinan (Khusus Level Manajerial)', 'label' => 'Delegasi', 'sort_order' => 120, 'is_active' => true, 'managerial_only' => true],
            ['key' => 'leadership_development', 'group' => 'Kepemimpinan (Khusus Level Manajerial)', 'label' => 'Pengembangan Anggota', 'sort_order' => 130, 'is_active' => true, 'managerial_only' => true],
        ];
    }
}
