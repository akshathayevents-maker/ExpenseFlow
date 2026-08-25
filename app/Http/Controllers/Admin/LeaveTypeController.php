<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLeaveTypeRequest;
use App\Http\Requests\Admin\UpdateLeaveTypeRequest;
use App\Models\LeaveType;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

// Admin-only leave type configuration. Leave types are never deleted — only
// deactivated via is_active — so this controller intentionally has no
// destroy() action, matching the safety rule that leave data (and the
// leave types it references) is never removed.
class LeaveTypeController extends Controller
{
    public function index(): View
    {
        $leaveTypes = LeaveType::orderBy('name')->get();

        return view('admin.leave-types.index', compact('leaveTypes'));
    }

    public function create(): View
    {
        return view('admin.leave-types.create');
    }

    public function store(StoreLeaveTypeRequest $request): RedirectResponse
    {
        LeaveType::create($this->withBooleanDefaults($request->validated()));

        return redirect()->route('admin.leave-types.index')
            ->with('success', 'Leave type created.');
    }

    public function edit(LeaveType $leaveType): View
    {
        return view('admin.leave-types.edit', compact('leaveType'));
    }

    public function update(UpdateLeaveTypeRequest $request, LeaveType $leaveType): RedirectResponse
    {
        $leaveType->update($this->withBooleanDefaults($request->validated()));

        return redirect()->route('admin.leave-types.index')
            ->with('success', 'Leave type updated.');
    }

    // Unchecked HTML checkboxes are simply absent from the request — fill
    // in an explicit false for each boolean flag so "unchecked" always
    // persists as false rather than being silently skipped by ->update()/
    // ->create() (which would leave a prior true value untouched on edit).
    private function withBooleanDefaults(array $data): array
    {
        foreach (['is_active', 'allow_half_day', 'is_paid', 'allow_carry_forward'] as $flag) {
            $data[$flag] = (bool) ($data[$flag] ?? false);
        }

        return $data;
    }
}
