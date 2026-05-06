<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LmsPerformanceAppraisal;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LmsPerformanceAppraisalController extends Controller
{
    public function index(Request $request)
    {
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
            $appraisals->getCollection()->map(function (LmsPerformanceAppraisal $a) {
                $fields = [
                    $a->quality_work,
                    $a->quantity_work,
                    $a->task_knowledge,
                    $a->discipline,
                    $a->teamwork,
                    $a->communication,
                    $a->initiative,
                    $a->target_realization,
                    $a->time_management,
                    $a->attitude,
                    $a->adaptability,
                    $a->leadership_delegation,
                    $a->leadership_development,
                ];

                $values = array_values(array_filter($fields, fn ($v) => $v !== null));
                $count = count($values);
                $total = array_sum($values);
                $avg = $count > 0 ? round($total / $count, 2) : null;

                $a->setAttribute('score_total', $total);
                $a->setAttribute('score_count', $count);
                $a->setAttribute('score_avg', $avg);

                return $a;
            })
        );

        return Inertia::render('admin/Lms/PerformanceAppraisal/Index', [
            'appraisals' => $appraisals,
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
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'evaluated_at' => 'required|date',
            'quality_work' => 'required|integer|min:1|max:5',
            'quantity_work' => 'required|integer|min:1|max:5',
            'task_knowledge' => 'required|integer|min:1|max:5',
            'discipline' => 'required|integer|min:1|max:5',
            'teamwork' => 'required|integer|min:1|max:5',
            'communication' => 'required|integer|min:1|max:5',
            'initiative' => 'required|integer|min:1|max:5',
            'target_realization' => 'required|integer|min:1|max:5',
            'time_management' => 'required|integer|min:1|max:5',
            'attitude' => 'required|integer|min:1|max:5',
            'adaptability' => 'required|integer|min:1|max:5',
            'leadership_delegation' => 'nullable|integer|min:1|max:5',
            'leadership_development' => 'nullable|integer|min:1|max:5',
            'feedback' => 'nullable|string',
        ]);

        $employee = User::query()->whereKey($validated['user_id'])->firstOrFail();
        $isManager = $employee->subordinates()->exists();

        if ($isManager) {
            if (! array_key_exists('leadership_delegation', $validated) || $validated['leadership_delegation'] === null) {
                return back()->withErrors(['leadership_delegation' => 'Wajib diisi untuk level manajerial.'])->withInput();
            }
            if (! array_key_exists('leadership_development', $validated) || $validated['leadership_development'] === null) {
                return back()->withErrors(['leadership_development' => 'Wajib diisi untuk level manajerial.'])->withInput();
            }
        }

        $validated['evaluator_id'] = auth()->id();

        LmsPerformanceAppraisal::create($validated);

        return redirect()->route('admin.lms-performance-appraisals.index')
            ->with('success', 'Performance appraisal berhasil disimpan.');
    }
}

