<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SemesterReport;
use App\Models\User;
use App\Services\SemesterReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SemesterReportController extends Controller
{
    public function __construct(private SemesterReportService $reportService) {}

    public function index(Request $request)
    {
        $year = (int) ($request->get('year', now()->year));
        $semester = (int) ($request->get('semester', now()->month <= 6 ? 1 : 2));

        $reports = SemesterReport::query()
            ->with(['user.position', 'user.department'])
            ->period($year, $semester)
            ->when($request->get('grade'), fn ($q, $g) => $q->where('grade', $g))
            ->join('users', 'users.id', '=', 'semester_reports.user_id')
            ->orderByDesc('semester_reports.final_score')
            ->select('semester_reports.*')
            ->paginate(15)
            ->withQueryString();

        $gradeStats = SemesterReport::query()
            ->period($year, $semester)
            ->selectRaw('grade, count(*) as total')
            ->groupBy('grade')
            ->pluck('total', 'grade');

        return Inertia::render('admin/Lms/SemesterReport/Index', [
            'reports' => $reports,
            'gradeStats' => $gradeStats,
            'filters' => [
                'year' => $year,
                'semester' => $semester,
                'grade' => $request->get('grade', ''),
            ],
        ]);
    }

    public function show(SemesterReport $semester_report)
    {
        $semester_report->load(['user.position', 'user.department', 'generator:id,name']);

        return Inertia::render('admin/Lms/SemesterReport/Show', [
            'report' => $semester_report,
        ]);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'year' => ['required', 'integer', 'min:2020', 'max:' . (now()->year + 1)],
            'semester' => ['required', 'in:1,2'],
            'user_id' => ['nullable', 'exists:users,id'],
        ]);

        $year = (int) $validated['year'];
        $semester = (int) $validated['semester'];

        if (! empty($validated['user_id'])) {
            $user = User::findOrFail($validated['user_id']);
            $this->reportService->generate($user, $year, $semester);

            $message = 'Raport ' . $user->name . ' berhasil di-generate.';
        } else {
            $count = $this->reportService->generateForAll($year, $semester);
            $message = $count . ' raport semester berhasil di-generate (draft).';
        }

        return redirect()->route('admin.semester-reports.index', ['year' => $year, 'semester' => $semester])
            ->with('success', $message);
    }

    public function publish(SemesterReport $semester_report)
    {
        $this->reportService->publish($semester_report);

        return redirect()->back()->with('success', 'Raport diterbitkan — sudah terlihat oleh karyawan.');
    }

    public function bulkPublish(Request $request)
    {
        $validated = $request->validate([
            'year' => ['required', 'integer'],
            'semester' => ['required', 'in:1,2'],
        ]);

        $count = SemesterReport::query()
            ->period((int) $validated['year'], (int) $validated['semester'])
            ->where('status', SemesterReport::STATUS_DRAFT)
            ->update([
                'status' => SemesterReport::STATUS_PUBLISHED,
                'published_at' => now(),
            ]);

        return redirect()->back()->with('success', $count . ' raport diterbitkan sekaligus.');
    }

    public function downloadPdf(SemesterReport $semester_report)
    {
        $semester_report->load(['user.position', 'user.department', 'generator:id,name']);
        $rec = $semester_report->recommendation ?? [];
        $semesterLabel = $semester_report->semester === '1' ? 'Semester I (Jan–Jun)' : 'Semester II (Jul–Des)';
        $gradeLabel = SemesterReport::gradeLabel($semester_report->grade);
        $safeName = str_replace([' ', '/'], '-', $semester_report->user->name ?? 'raport');

        return Pdf::loadView('pdf.semester-report', [
            'report' => $semester_report,
            'rec' => $rec,
            'semesterLabel' => $semesterLabel,
            'gradeLabel' => $gradeLabel,
        ])->setPaper('a4', 'portrait')->download("Raport-{$safeName}-{$semester_report->year}-S{$semester_report->semester}.pdf");
    }

    public function execute(SemesterReport $semester_report)
    {
        $result = $this->reportService->executeRecommendation($semester_report, auth()->user());

        if (! empty($result['already_executed'])) {
            return redirect()->back()->with('success', $result['message']);
        }

        if (! empty($result['skipped']) && ! empty($result['blocked_by_freeze'])) {
            return redirect()->back()->with('success', $result['message']);
        }

        if (! empty($result['skipped'])) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('success', $result['message']);
    }
}
