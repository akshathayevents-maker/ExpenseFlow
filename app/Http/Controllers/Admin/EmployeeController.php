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

    public function show(User $employee): View
    {
        return view('admin.employees.show', compact('employee'));
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // role/is_active are not in User::$fillable (prevents mass-assignment via
        // other endpoints). Explicit assignment is required here for authorized admin action.
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'phone'    => $data['phone'] ?? null,
            'password' => $data['password'],
        ]);
        $user->role      = $data['role'];
        $user->is_active = $data['is_active'] ?? true;
        $user->save();

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

        // Explicitly assign fillable fields only
        $employee->name  = $data['name'];
        $employee->email = $data['email'];
        $employee->phone = $data['phone'] ?? null;

        if (! empty($data['password'])) {
            $employee->password = $data['password'];
        }

        // role/is_active are not in $fillable — explicit assignment for authorized admin action
        $employee->role      = $data['role'];
        $employee->is_active = (bool) ($data['is_active'] ?? $employee->is_active);

        $employee->save();

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
