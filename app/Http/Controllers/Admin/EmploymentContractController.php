<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmploymentContract;
use App\Models\User;
use App\Services\ContractAlertService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmploymentContractController extends Controller
{
    public function __construct(private ContractAlertService $alertService) {}

    public function index(Request $request)
    {
        $contracts = EmploymentContract::query()
            ->with(['user.position', 'user.department'])
            ->when($request->get('type'), fn ($q, $type) => $q->where('type', $type))
            ->when($request->get('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->get('search'), function ($q, $search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('contract_number', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->orderBy('end_date')
            ->latest('start_date')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/Contracts/Index', [
            'contracts' => $contracts,
            'stats' => $this->alertService->getStats(),
            'filters' => [
                'type' => $request->get('type', ''),
                'status' => $request->get('status', ''),
                'search' => $request->get('search', ''),
            ],
        ]);
    }

    public function create(Request $request)
    {
        $users = User::query()->active()->orderBy('name')->get(['id', 'name', 'employee_id']);

        return Inertia::render('admin/Contracts/Create', [
            'users' => $users,
            'preselectUserId' => $request->get('user_id'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'type' => ['required', 'in:pkwt,pkwtt'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date', 'required_if:type,pkwt'],
            'salary_grade' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);

        $sequence = EmploymentContract::where('user_id', $validated['user_id'])->count() + 1;
        $prefix = $validated['type'] === 'pkwtt' ? 'PKWTT' : 'PKWT';

        EmploymentContract::create([
            ...$validated,
            'end_date' => $validated['type'] === 'pkwtt' ? null : $validated['end_date'],
            'contract_number' => sprintf('%s/%s/%03d', $prefix, now()->format('Ym'), $sequence),
            'status' => EmploymentContract::STATUS_ACTIVE,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.contracts.index')
            ->with('success', 'Kontrak berhasil dibuat.');
    }

    public function edit(EmploymentContract $contract)
    {
        $users = User::query()->active()->orderBy('name')->get(['id', 'name', 'employee_id']);

        return Inertia::render('admin/Contracts/Edit', [
            'contract' => $contract->load('user'),
            'users' => $users,
        ]);
    }

    public function update(Request $request, EmploymentContract $contract)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:pkwt,pkwtt'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date', 'required_if:type,pkwt'],
            'status' => ['required', 'in:active,expired,renewed,terminated'],
            'salary_grade' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['end_date'] = $validated['type'] === 'pkwtt' ? null : ($validated['end_date'] ?? null);

        $contract->update($validated);

        return redirect()->route('admin.contracts.index')
            ->with('success', 'Kontrak berhasil diperbarui.');
    }

    public function destroy(EmploymentContract $contract)
    {
        $contract->delete();

        return redirect()->route('admin.contracts.index')
            ->with('success', 'Kontrak berhasil dihapus.');
    }

    public function alerts()
    {
        return Inertia::render('admin/Contracts/Alerts', [
            'alerts' => $this->alertService->getAlerts(),
            'stats' => $this->alertService->getStats(),
        ]);
    }

    public function renew(Request $request, EmploymentContract $contract)
    {
        $validated = $request->validate([
            'new_end_date' => ['required', 'date', 'after:start_date'],
            'salary_grade' => ['nullable', 'string', 'max:50'],
        ]);

        $this->alertService->renewContract($contract, $validated['new_end_date'], $validated['salary_grade'] ?? null);

        return redirect()->route('admin.contracts.index')
            ->with('success', 'Kontrak berhasil diperpanjang.');
    }

    public function convertToPkwtt(EmploymentContract $contract)
    {
        $newContract = $this->alertService->convertToPkwtt($contract);

        return redirect()->route('admin.contracts.index')
            ->with('success', 'Karyawan diangkat menjadi PKWTT (' . $newContract->contract_number . ').');
    }
}
