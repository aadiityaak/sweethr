<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LmsPerformanceAppraisalParameter;
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

    public function index()
    {
        $parameters = LmsPerformanceAppraisalParameter::query()
            ->orderBy('sort_order')
            ->orderBy('group')
            ->orderBy('label')
            ->get();

        return Inertia::render('admin/Lms/PerformanceAppraisalParameter/Index', [
            'parameters' => $parameters,
        ]);
    }

    public function create()
    {
        $existingKeys = LmsPerformanceAppraisalParameter::query()->pluck('key')->all();
        $availableKeys = array_values(array_filter(self::ALLOWED_KEYS, fn ($k) => ! in_array($k, $existingKeys, true)));

        return Inertia::render('admin/Lms/PerformanceAppraisalParameter/Create', [
            'availableKeys' => $availableKeys,
            'groups' => self::DEFAULT_GROUPS,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'in:'.implode(',', self::ALLOWED_KEYS), 'unique:lms_performance_appraisal_parameters,key'],
            'group' => ['required', 'string', 'max:255'],
            'label' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['nullable', 'boolean'],
            'managerial_only' => ['nullable', 'boolean'],
        ]);

        $key = (string) $validated['key'];
        $isLeadership = in_array($key, ['leadership_delegation', 'leadership_development'], true);
        if ($isLeadership) {
            $validated['managerial_only'] = true;
        }

        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);
        $validated['managerial_only'] = (bool) ($validated['managerial_only'] ?? false);

        LmsPerformanceAppraisalParameter::create($validated);

        return redirect()->route('admin.lms-performance-appraisal-parameters.index')
            ->with('success', 'Parameter penilaian berhasil ditambahkan.');
    }

    public function edit(LmsPerformanceAppraisalParameter $lms_performance_appraisal_parameter)
    {
        return Inertia::render('admin/Lms/PerformanceAppraisalParameter/Edit', [
            'parameter' => $lms_performance_appraisal_parameter,
            'groups' => self::DEFAULT_GROUPS,
        ]);
    }

    public function update(Request $request, LmsPerformanceAppraisalParameter $lms_performance_appraisal_parameter)
    {
        $validated = $request->validate([
            'group' => ['required', 'string', 'max:255'],
            'label' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['nullable', 'boolean'],
            'managerial_only' => ['nullable', 'boolean'],
        ]);

        $key = (string) $lms_performance_appraisal_parameter->key;
        $isLeadership = in_array($key, ['leadership_delegation', 'leadership_development'], true);
        if ($isLeadership) {
            $validated['managerial_only'] = true;
        }

        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);
        $validated['managerial_only'] = (bool) ($validated['managerial_only'] ?? false);

        $lms_performance_appraisal_parameter->update($validated);

        return redirect()->route('admin.lms-performance-appraisal-parameters.index')
            ->with('success', 'Parameter penilaian berhasil diperbarui.');
    }

    public function destroy(LmsPerformanceAppraisalParameter $lms_performance_appraisal_parameter)
    {
        $lms_performance_appraisal_parameter->delete();

        return redirect()->route('admin.lms-performance-appraisal-parameters.index')
            ->with('success', 'Parameter penilaian berhasil dihapus.');
    }
}

