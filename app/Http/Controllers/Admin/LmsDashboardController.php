<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DisciplinaryAction;
use App\Models\EmployeeViolation;
use App\Models\SemesterReport;
use App\Services\ContractAlertService;
use App\Services\DisciplinaryPointService;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class LmsDashboardController extends Controller
{
    public function __construct(
        private ContractAlertService $contractService,
        private DisciplinaryPointService $pointService,
    ) {}

    /**
     * Executive Management View — 5 widget utama.
     */
    public function index()
    {
        // Widget 1: Personnel Status (PKWT vs PKWTT + alert kontrak)
        $contractStats = $this->contractService->getStats();
        $contractAlerts = $this->contractService->getExpiringContracts(60)->take(8);

        // Widget 2: Outlet Training Progress (% penyelesaian LMS per outlet/departemen)
        $outletProgress = $this->getOutletTrainingProgress();

        // Widget 3: Disciplinary & Violation Feed
        $monthlyFeed = $this->pointService->getMonthlyPointsFeed(6);
        $recentViolations = EmployeeViolation::query()
            ->with(['user:id,name', 'violation:id,name,category'])
            ->latest('occurred_at')
            ->limit(8)
            ->get();

        // Widget 4: Automated SP Generator (poin >= 35 tapi belum ada SP1 aktif)
        $spCandidates = $this->getSpCandidates();

        // Widget 5: Semester Report Card & Reward Engine
        $currentYear = now()->year;
        $currentSemester = now()->month <= 6 ? 1 : 2;
        $topReports = SemesterReport::query()
            ->with(['user:id,name,position_id', 'user.position:id,title'])
            ->period($currentYear, $currentSemester)
            ->where('status', SemesterReport::STATUS_PUBLISHED)
            ->orderByDesc('final_score')
            ->limit(10)
            ->get();

        return Inertia::render('admin/LmsDashboard/Index', [
            'contractStats' => $contractStats,
            'contractAlerts' => $contractAlerts,
            'outletProgress' => $outletProgress,
            'monthlyFeed' => $monthlyFeed,
            'recentViolations' => $recentViolations,
            'spCandidates' => $spCandidates,
            'topReports' => $topReports,
            'currentPeriod' => ['year' => $currentYear, 'semester' => $currentSemester],
        ]);
    }

    /**
     * % penyelesaian LMS per outlet (departemen) — quiz pass rate + material reads.
     */
    private function getOutletTrainingProgress(): array
    {
        $rows = DB::table('users')
            ->join('departments', 'departments.id', '=', 'users.department_id')
            ->leftJoin('lms_quiz_attempts', function ($join) {
                $join->on('lms_quiz_attempts.user_id', '=', 'users.id')
                    ->where('lms_quiz_attempts.is_passed', true);
            })
            ->leftJoin('lms_material_reads', 'lms_material_reads.user_id', '=', 'users.id')
            ->where('users.employment_status', 'active')
            ->groupBy('departments.id', 'departments.name')
            ->selectRaw("
                departments.id,
                departments.name,
                count(distinct users.id) as total_employees,
                count(distinct lms_quiz_attempts.id) as passed_quizzes,
                count(distinct lms_material_reads.id) as material_reads
            ")
            ->get();

        return $rows->map(function ($row) {
            $employees = max(1, (int) $row->total_employees);

            return [
                'outlet' => $row->name,
                'total_employees' => (int) $row->total_employees,
                'passed_quizzes' => (int) $row->passed_quizzes,
                'material_reads' => (int) $row->material_reads,
                'completion_pct' => (int) min(100, round((($row->passed_quizzes * 2) + $row->material_reads) / ($employees * 10) * 100)),
            ];
        })->sortByDesc('completion_pct')->values()->all();
    }

    /**
     * Karyawan dengan poin >= 35 yang belum punya SP1 aktif → kandidat SP otomatis.
     */
    private function getSpCandidates(): array
    {
        return EmployeeViolation::query()
            ->selectRaw('users.id, users.name, sum(employee_violations.points) as total_points')
            ->join('users', 'users.id', '=', 'employee_violations.user_id')
            ->where('employee_violations.occurred_at', '>=', now()->subMonths(6))
            ->where('users.employment_status', 'active')
            ->groupBy('users.id', 'users.name')
            ->havingRaw('sum(employee_violations.points) >= ?', [DisciplinaryPointService::THRESHOLD_SP1])
            ->orderByDesc('total_points')
            ->get()
            ->filter(function ($row) {
                $hasActiveSp1 = DisciplinaryAction::forUser($row->id)
                    ->where('action_type', DisciplinaryAction::TYPE_SP1)
                    ->whereIn('status', [DisciplinaryAction::STATUS_ACTIVE, DisciplinaryAction::STATUS_RESOLVED])
                    ->exists();

                return ! $hasActiveSp1;
            })
            ->take(10)
            ->values()
            ->all();
    }
}
