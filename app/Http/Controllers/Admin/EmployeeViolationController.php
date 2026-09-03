<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DisciplinaryAction;
use App\Models\DisciplinaryViolation;
use App\Models\EmployeeViolation;
use App\Models\User;
use App\Services\DisciplinaryPointService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmployeeViolationController extends Controller
{
    public function __construct(private DisciplinaryPointService $pointService) {}

    /**
     * Feed pelanggaran (semua karyawan).
     */
    public function index(Request $request)
    {
        $violations = EmployeeViolation::query()
            ->with(['user.position', 'violation', 'reporter:id,name'])
            ->when($request->get('user'), fn ($q, $userId) => $q->where('user_id', $userId))
            ->when($request->get('category'), function ($q, $category) {
                $q->whereHas('violation', fn ($vq) => $vq->where('category', $category));
            })
            ->when($request->get('search'), function ($q, $search) {
                $q->whereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('violation', fn ($vq) => $vq->where('name', 'like', "%{$search}%"));
            })
            ->latest('occurred_at')
            ->paginate(15)
            ->withQueryString();

        $masterViolations = DisciplinaryViolation::query()->active()->orderBy('category')->orderBy('code')->get();
        $users = User::query()->active()->orderBy('name')->get(['id', 'name']);

        return Inertia::render('admin/Disciplinary/Records/Index', [
            'violations' => $violations,
            'masterViolations' => $masterViolations,
            'users' => $users,
            'topViolators' => $this->pointService->getTopViolators(10),
            'monthlyFeed' => $this->pointService->getMonthlyPointsFeed(6),
            'filters' => [
                'user' => $request->get('user', ''),
                'category' => $request->get('category', ''),
                'search' => $request->get('search', ''),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'disciplinary_violation_id' => ['required', 'exists:disciplinary_violations,id'],
            'occurred_at' => ['required', 'date', 'before_or_equal:now'],
            'notes' => ['nullable', 'string'],
        ]);

        $user = User::findOrFail($validated['user_id']);
        $violation = DisciplinaryViolation::active()->findOrFail($validated['disciplinary_violation_id']);

        $this->pointService->recordViolation(
            $user,
            $violation,
            new \DateTimeImmutable($validated['occurred_at']),
            $validated['notes'] ?? null,
            null,
            $request->user()
        );

        return redirect()->route('admin.employee-violations.index')
            ->with('success', 'Pelanggaran tercatat (' . $violation->points . ' poin).');
    }

    public function destroy(EmployeeViolation $employee_violation)
    {
        $employee_violation->delete();

        return redirect()->route('admin.employee-violations.index')
            ->with('success', 'Catatan pelanggaran dihapus.');
    }

    /**
     * Daftar SP / aksi disiplin + konfirmasi HR.
     */
    public function actionsIndex(Request $request)
    {
        $actions = DisciplinaryAction::query()
            ->with(['user.position', 'issuer:id,name', 'confirmer:id,name'])
            ->when($request->get('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->get('type'), fn ($q, $type) => $q->where('action_type', $type))
            ->latest('issued_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/Disciplinary/Actions/Index', [
            'actions' => $actions,
            'filters' => [
                'status' => $request->get('status', ''),
                'type' => $request->get('type', ''),
            ],
        ]);
    }

    public function confirmAction(Request $request, DisciplinaryAction $disciplinary_action)
    {
        $this->pointService->confirmAction($disciplinary_action, $request->user());

        return redirect()->back()->with('success', 'SP dikonfirmasi / ditandatangani.');
    }

    public function revokeAction(DisciplinaryAction $disciplinary_action)
    {
        $disciplinary_action->update(['status' => DisciplinaryAction::STATUS_REVOKED]);

        return redirect()->back()->with('success', 'Aksi disiplin dicabut.');
    }

    public function resolveAction(DisciplinaryAction $disciplinary_action)
    {
        $disciplinary_action->update(['status' => DisciplinaryAction::STATUS_RESOLVED]);

        return redirect()->back()->with('success', 'Aksi disiplin diselesaikan.');
    }
}
