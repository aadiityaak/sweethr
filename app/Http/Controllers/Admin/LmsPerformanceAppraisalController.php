<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LmsPerformanceAppraisal;
use App\Models\LmsPerformanceAppraisalParameter;
use App\Models\User;
use Illuminate\Http\Request;
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
            ->with(['position:id,title'])
            ->orderBy('name')
            ->get(['id', 'name', 'employee_id', 'position_id']);

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
            ->with(['position:id,title'])
            ->orderBy('name')
            ->get(['id', 'name', 'employee_id', 'position_id']);

        $lms_performance_appraisal->load(['user:id,name,employee_id,position_id', 'user.position:id,title']);

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
        $userId = (int) $request->input('user_id');
        $employee = User::query()->whereKey($userId)->firstOrFail();
        $positionId = $employee->position_id ? (int) $employee->position_id : null;

        $visibleKeys = collect($this->activeParameters())
            ->filter(function ($p) use ($positionId) {
                $ids = $p['visible_position_ids'] ?? [];
                if (! is_array($ids) || count($ids) === 0) {
                    return true;
                }
                if ($positionId === null) {
                    return false;
                }
                return in_array($positionId, $ids, true);
            })
            ->pluck('key')
            ->values()
            ->all();

        $rules = [
            'user_id' => 'required|exists:users,id',
            'evaluated_at' => 'required|date',
            'feedback' => 'nullable|string',
        ];

        foreach (self::SCORE_KEYS as $key) {
            $rules[$key] = in_array($key, $visibleKeys, true)
                ? 'required|integer|min:1|max:5'
                : 'nullable|integer|min:1|max:5';
        }

        $validated = $request->validate($rules);

        foreach (self::SCORE_KEYS as $key) {
            if (! in_array($key, $visibleKeys, true)) {
                $validated[$key] = null;
            }
        }

        return $validated;
    }

    private function activeParameters(): array
    {
        $parameters = LmsPerformanceAppraisalParameter::query()
            ->where('is_active', true)
            ->orderBy('group')
            ->orderBy('id')
            ->get(['key', 'group', 'label', 'is_active', 'managerial_only', 'visible_position_ids'])
            ->map(fn (LmsPerformanceAppraisalParameter $p) => [
                'key' => $p->key,
                'group' => $p->group,
                'label' => $p->label,
                'is_active' => (bool) $p->is_active,
                'managerial_only' => (bool) $p->managerial_only,
                'visible_position_ids' => is_array($p->visible_position_ids) ? array_values(array_map('intval', $p->visible_position_ids)) : [],
            ])
            ->values()
            ->all();

        if (count($parameters) > 0) {
            return $parameters;
        }

        return [
            ['key' => 'quality_work', 'group' => 'Kompetensi Teknis (Hard Skills)', 'label' => 'Kualitas Kerja', 'is_active' => true, 'managerial_only' => false, 'visible_position_ids' => []],
            ['key' => 'quantity_work', 'group' => 'Kompetensi Teknis (Hard Skills)', 'label' => 'Kuantitas Kerja', 'is_active' => true, 'managerial_only' => false, 'visible_position_ids' => []],
            ['key' => 'task_knowledge', 'group' => 'Kompetensi Teknis (Hard Skills)', 'label' => 'Pengetahuan Tugas', 'is_active' => true, 'managerial_only' => false, 'visible_position_ids' => []],
            ['key' => 'discipline', 'group' => 'Perilaku Kerja (Soft Skills)', 'label' => 'Kedisiplinan', 'is_active' => true, 'managerial_only' => false, 'visible_position_ids' => []],
            ['key' => 'teamwork', 'group' => 'Perilaku Kerja (Soft Skills)', 'label' => 'Kerja Sama Tim', 'is_active' => true, 'managerial_only' => false, 'visible_position_ids' => []],
            ['key' => 'communication', 'group' => 'Perilaku Kerja (Soft Skills)', 'label' => 'Komunikasi', 'is_active' => true, 'managerial_only' => false, 'visible_position_ids' => []],
            ['key' => 'initiative', 'group' => 'Perilaku Kerja (Soft Skills)', 'label' => 'Inisiatif', 'is_active' => true, 'managerial_only' => false, 'visible_position_ids' => []],
            ['key' => 'target_realization', 'group' => 'Pencapaian Target (KPI)', 'label' => 'Realisasi Target', 'is_active' => true, 'managerial_only' => false, 'visible_position_ids' => []],
            ['key' => 'time_management', 'group' => 'Pencapaian Target (KPI)', 'label' => 'Manajemen Waktu', 'is_active' => true, 'managerial_only' => false, 'visible_position_ids' => []],
            ['key' => 'attitude', 'group' => 'Sikap dan Adaptabilitas', 'label' => 'Sikap (Attitude)', 'is_active' => true, 'managerial_only' => false, 'visible_position_ids' => []],
            ['key' => 'adaptability', 'group' => 'Sikap dan Adaptabilitas', 'label' => 'Adaptabilitas', 'is_active' => true, 'managerial_only' => false, 'visible_position_ids' => []],
            ['key' => 'leadership_delegation', 'group' => 'Kepemimpinan (Khusus Level Manajerial)', 'label' => 'Delegasi', 'is_active' => true, 'managerial_only' => true, 'visible_position_ids' => []],
            ['key' => 'leadership_development', 'group' => 'Kepemimpinan (Khusus Level Manajerial)', 'label' => 'Pengembangan Anggota', 'is_active' => true, 'managerial_only' => true, 'visible_position_ids' => []],
        ];
    }
}
