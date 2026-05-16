<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEmployeeRequest;
use App\Http\Requests\Admin\UpdateEmployeeRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->get('search', '');
        $role   = $request->get('role', '');
        $status = $request->get('status', '');

        $employees = User::whereIn('role', ['employee', 'manager'])
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('name',  'ilike', "%{$search}%")
                ->orWhere('email', 'ilike', "%{$search}%")
                ->orWhere('phone', 'ilike', "%{$search}%")
            ))
            ->when($role,   fn ($q, $v) => $q->where('role', $v))
            ->when($status !== '', fn ($q) => $q->where('is_active', $status === 'active'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        // Global workforce stats (unfiltered — always reflects full picture)
        $stats = [
            'total'    => User::whereIn('role', ['employee', 'manager'])->count(),
            'managers' => User::where('role', 'manager')->count(),
            'active'   => User::whereIn('role', ['employee', 'manager'])->where('is_active', true)->count(),
            'inactive' => User::whereIn('role', ['employee', 'manager'])->where('is_active', false)->count(),
            'recent'   => User::whereIn('role', ['employee', 'manager'])
                              ->where('created_at', '>=', now()->subDays(30))->count(),
        ];

        return view('admin.employees.index',
            compact('employees', 'search', 'role', 'status', 'stats'));
    }

    public function create(): View
    {
        return view('admin.employees.create');
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        User::create($request->validated());

        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee created successfully.');
    }

    public function edit(User $employee): View
    {
        return view('admin.employees.edit', compact('employee'));
    }

    public function update(UpdateEmployeeRequest $request, User $employee): RedirectResponse
    {
        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $employee->update($data);

        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee updated successfully.');
    }

    public function destroy(User $employee): RedirectResponse
    {
        if ($employee->id === auth()->id()) {
            return back()->with('error', 'Cannot delete your own account.');
        }

        $employee->delete();

        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee deleted successfully.');
    }

    public function toggleStatus(User $employee): RedirectResponse
    {
        if ($employee->id === auth()->id()) {
            return back()->with('error', 'Cannot deactivate your own account.');
        }

        $employee->update(['is_active' => ! $employee->is_active]);

        $status = $employee->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Employee {$status} successfully.");
    }
}
