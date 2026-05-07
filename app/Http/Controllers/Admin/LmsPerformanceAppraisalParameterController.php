<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LmsPerformanceAppraisalParameter;
use App\Models\Position;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LmsPerformanceAppraisalParameterController extends Controller
{
    private const ALLOWED_KEYS = [
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

    private const DEFAULT_GROUPS = [
        'Kompetensi Teknis (Hard Skills)',
        'Perilaku Kerja (Soft Skills)',
        'Pencapaian Target (KPI)',
        'Sikap dan Adaptabilitas',
        'Kepemimpinan (Khusus Level Manajerial)',
    ];

    public function index(Request $request)
    {
        $group = trim((string) $request->get('group', ''));

        $groups = LmsPerformanceAppraisalParameter::query()
            ->select('group')
            ->distinct()
            ->orderBy('group')
            ->pluck('group')
            ->values();

        $positions = Position::query()
            ->active()
            ->orderBy('title')
            ->get(['id', 'title']);

        $query = LmsPerformanceAppraisalParameter::query();

        if ($group !== '') {
            $query->where('group', $group);
        }

        $parameters = $query
            ->orderBy('group')
            ->orderBy('id')
            ->get();

        return Inertia::render('admin/Lms/PerformanceAppraisalParameter/Index', [
            'parameters' => $parameters,
            'positions' => $positions,
            'groups' => $groups,
            'filters' => [
                'group' => $group !== '' ? $group : null,
            ],
        ]);
    }

    public function create()
    {
        $existingKeys = LmsPerformanceAppraisalParameter::query()->pluck('key')->all();
        $availableKeys = array_values(array_filter(self::ALLOWED_KEYS, fn ($k) => ! in_array($k, $existingKeys, true)));

        $positions = Position::query()
            ->active()
            ->orderBy('title')
            ->get(['id', 'title']);

        return Inertia::render('admin/Lms/PerformanceAppraisalParameter/Create', [
            'availableKeys' => $availableKeys,
            'groups' => self::DEFAULT_GROUPS,
            'positions' => $positions,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'in:'.implode(',', self::ALLOWED_KEYS), 'unique:lms_performance_appraisal_parameters,key'],
            'group' => ['required', 'string', 'max:255'],
            'label' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'visible_position_ids' => ['nullable', 'array'],
            'visible_position_ids.*' => ['integer', 'exists:positions,id'],
        ]);

        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);
        $validated['visible_position_ids'] = array_values(array_unique(array_map('intval', $validated['visible_position_ids'] ?? [])));
        if (count($validated['visible_position_ids']) === 0) {
            $validated['visible_position_ids'] = null;
        }

        LmsPerformanceAppraisalParameter::create($validated);

        return redirect()->route('admin.lms-performance-appraisal-parameters.index')
            ->with('success', 'Parameter penilaian berhasil ditambahkan.');
    }

    public function edit(LmsPerformanceAppraisalParameter $parameter)
    {
        return Inertia::render('admin/Lms/PerformanceAppraisalParameter/Edit', [
            'parameter' => $parameter,
            'groups' => self::DEFAULT_GROUPS,
            'positions' => Position::query()->active()->orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function update(Request $request, LmsPerformanceAppraisalParameter $parameter)
    {
        $validated = $request->validate([
            'group' => ['required', 'string', 'max:255'],
            'label' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'visible_position_ids' => ['nullable', 'array'],
            'visible_position_ids.*' => ['integer', 'exists:positions,id'],
        ]);

        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);
        $validated['visible_position_ids'] = array_values(array_unique(array_map('intval', $validated['visible_position_ids'] ?? [])));
        if (count($validated['visible_position_ids']) === 0) {
            $validated['visible_position_ids'] = null;
        }

        $parameter->update($validated);

        return redirect()->route('admin.lms-performance-appraisal-parameters.index')
            ->with('success', 'Parameter penilaian berhasil diperbarui.');
    }

    public function destroy(LmsPerformanceAppraisalParameter $parameter)
    {
        $parameter->delete();

        return redirect()->route('admin.lms-performance-appraisal-parameters.index')
            ->with('success', 'Parameter penilaian berhasil dihapus.');
    }
}
