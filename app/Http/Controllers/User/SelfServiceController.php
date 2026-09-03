<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\DisciplinaryAction;
use App\Models\EmployeeViolation;
use App\Models\EmploymentContract;
use App\Models\SemesterReport;
use App\Services\DisciplinaryPointService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SelfServiceController extends Controller
{
    public function __construct(private DisciplinaryPointService $pointService) {}

    public function contract(Request $request)
    {
        $user = $request->user();

        $contracts = EmploymentContract::query()
            ->where('user_id', $user->id)
            ->orderByDesc('start_date')
            ->get();

        return Inertia::render('user/MyContract', [
            'contracts' => $contracts,
        ]);
    }

    public function disciplinaryRecord(Request $request)
    {
        $user = $request->user();

        $violations = EmployeeViolation::query()
            ->with(['violation:id,code,name,category'])
            ->where('user_id', $user->id)
            ->orderByDesc('occurred_at')
            ->limit(50)
            ->get();

        $actions = DisciplinaryAction::query()
            ->where('user_id', $user->id)
            ->orderByDesc('issued_at')
            ->get();

        return Inertia::render('user/MyDisciplinaryRecord', [
            'activePoints' => $this->pointService->getActivePoints($user),
            'breakdown' => $this->pointService->getPointsBreakdown($user),
            'cleanRecordBonus' => $this->pointService->getCleanRecordBonus($user),
            'violations' => $violations,
            'actions' => $actions,
        ]);
    }

    public function semesterReport(Request $request)
    {
        $reports = SemesterReport::query()
            ->published()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('year')
            ->orderByDesc('semester')
            ->get();

        return Inertia::render('user/MySemesterReport', [
            'reports' => $reports,
        ]);
    }
}
