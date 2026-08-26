<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\EmployeeAttendanceRegularization;
use App\Models\EmployeeOvertime;
use App\Models\ExpenseRequest;
use App\Models\LeaveRequest;
use App\Services\AdvanceEligibilityService;
use App\Services\EmployeeAttendanceService;
use App\Services\LeaveBalanceService;
use App\Services\WalletService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private WalletService $walletService,
        private EmployeeAttendanceService $attendanceService,
        private LeaveBalanceService $leaveBalanceService,
        private AdvanceEligibilityService $advanceEligibilityService,
    ) {}

    public function index(): View
    {
        $user   = auth()->user();
        $userId = $user->id;
        $today  = $this->attendanceService->today();

        $wallet = $this->walletService->getOrCreate($user);

        // ── Today's attendance state (reused, never re-derived) ──
        $dayState          = $this->attendanceService->getAttendanceDayState($user, $today);
        $todayIsNonWorking = $this->attendanceService->isTodayNonWorking();
        $todayCategory     = $this->attendanceService->todayCategory();
        $markableOtherHalf = $this->attendanceService->markableOtherHalfToday($user);

        $stats = [
            'my_requests'            => ExpenseRequest::where('requested_by', $userId)->count(),
            'pending_requests'       => ExpenseRequest::where('requested_by', $userId)->pending()->count(),
            'approved_requests'      => ExpenseRequest::where('requested_by', $userId)->approved()->count(),
            'approved_amount'        => ExpenseRequest::where('requested_by', $userId)->approved()->sum('amount'),
            'rejected_requests'      => ExpenseRequest::where('requested_by', $userId)->rejected()->count(),
            'monthly_expense'        => ExpenseRequest::where('requested_by', $userId)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount'),
            'reimbursement_pending'  => ExpenseRequest::where('requested_by', $userId)
                ->reimbursementPending()->sum('amount'),
            'wallet_balance'         => $wallet->balance,
            'wallet_low'             => $wallet->isLow(),
            'wallet_negative'        => $wallet->isNegative(),
        ];

        // ── Leave balances (reused service) ──
        $leaveBalances = $this->leaveBalanceService->balancesForAllTypes($user, $today);

        // ── Pending requests (employee's own) ──
        $pendingLeave = LeaveRequest::with('leaveType')
            ->where('user_id', $userId)->where('status', 'pending')
            ->latest('start_date')->limit(5)->get();
        $pendingRegularizations = EmployeeAttendanceRegularization::where('user_id', $userId)
            ->where('request_status', 'pending')
            ->latest('attendance_date')->limit(5)->get();
        $pendingOvertime = EmployeeOvertime::where('user_id', $userId)
            ->where('request_status', 'pending')
            ->latest('ot_date')->limit(5)->get();

        // ── Request summary chips (Leave requests, as the primary HR-request type) ──
        $leaveStatusCounts = LeaveRequest::where('user_id', $userId)
            ->selectRaw('status, count(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');
        $leaveCounts = [
            'pending'  => (int) ($leaveStatusCounts['pending'] ?? 0),
            'approved' => (int) ($leaveStatusCounts['approved'] ?? 0),
            'rejected' => (int) ($leaveStatusCounts['rejected'] ?? 0),
        ];

        // ── Upcoming approved leave ──
        $upcomingLeave = LeaveRequest::with('leaveType')
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->whereDate('end_date', '>=', $today->toDateString())
            ->orderBy('start_date')
            ->first();

        // ── Overtime summary (hours only — never monetary) ──
        $otSummary = [
            'approved_hours_this_month' => (float) EmployeeOvertime::where('user_id', $userId)
                ->where('request_status', 'approved')
                ->whereMonth('ot_date', $today->month)
                ->whereYear('ot_date', $today->year)
                ->sum('hours'),
            'pending_count' => EmployeeOvertime::where('user_id', $userId)
                ->where('request_status', 'pending')->count(),
        ];

        // ── Advance eligibility (amount only) ──
        $advanceEligibility = $this->advanceEligibilityService->evaluate($user, $today);

        // ── Recent activity across Leave / Regularization / OT ──
        $recentActivity = collect()
            ->concat(LeaveRequest::with('leaveType')->where('user_id', $userId)->whereIn('status', ['approved', 'rejected', 'cancelled'])->latest('updated_at')->limit(5)->get()->map(fn ($r) => [
                'type' => 'leave', 'label' => ($r->leaveType->name ?? 'Leave').' request',
                'status' => $r->status, 'date' => $r->updated_at, 'route' => route('employee.leave.show', $r),
            ]))
            ->concat(EmployeeAttendanceRegularization::where('user_id', $userId)->whereIn('request_status', ['approved', 'rejected', 'cancelled'])->latest('updated_at')->limit(5)->get()->map(fn ($r) => [
                'type' => 'regularization', 'label' => 'Attendance regularization',
                'status' => $r->request_status, 'date' => $r->updated_at, 'route' => route('employee.attendance-regularizations.show', $r),
            ]))
            ->concat(EmployeeOvertime::where('user_id', $userId)->whereIn('request_status', ['approved', 'rejected', 'cancelled'])->latest('updated_at')->limit(5)->get()->map(fn ($r) => [
                'type' => 'overtime', 'label' => 'Overtime request',
                'status' => $r->request_status, 'date' => $r->updated_at, 'route' => route('employee.overtime.show', $r),
            ]))
            ->sortByDesc('date')
            ->take(8)
            ->values();

        $recentRequests = ExpenseRequest::with(['category'])
            ->where('requested_by', $userId)
            ->latest()
            ->limit(5)
            ->get();

        return view('employee.dashboard', compact(
            'stats',
            'recentRequests',
            'dayState',
            'todayIsNonWorking',
            'todayCategory',
            'markableOtherHalf',
            'leaveBalances',
            'pendingLeave',
            'pendingRegularizations',
            'pendingOvertime',
            'leaveCounts',
            'upcomingLeave',
            'otSummary',
            'advanceEligibility',
            'recentActivity',
        ));
    }
}
