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
        $query = User::whereIn('role', ['employee', 'manager']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('phone', 'ilike', "%{$search}%");
            });
        }

        $employees = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.employees.index', compact('employees', 'search'));
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
