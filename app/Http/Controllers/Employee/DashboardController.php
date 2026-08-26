<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeAttendanceRegularization;
use App\Models\EmployeeAttendanceSegment;
use App\Models\EmployeeOvertime;
use App\Models\ExpenseRequest;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\AdvanceEligibilityService;
use App\Services\EmployeeAttendanceService;
use App\Services\LeaveBalanceService;
use App\Services\WalletService;
use Carbon\Carbon;
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
        $todayCard         = $this->resolveTodayCardState($dayState['attendance'] ?? null, $markableOtherHalf, $todayIsNonWorking, $user, $today);

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
            'todayCard',
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

    /**
     * Presentation-only mapping of today's attendance state (as already
     * computed by EmployeeAttendanceService::getAttendanceDayState()/
     * markableOtherHalfToday()) into one of 7 simplified card states for
     * the dashboard "Today" hero. This adds NO new attendance/leave rules —
     * it only decides which of the already-known facts to show and how to
     * word them. The one extra query below (today's complementary-half
     * EmployeeAttendanceSegment, if any) mirrors the identical lookup
     * already made inline by getMonthlyHistory() for past days; today just
     * doesn't have an equivalent value precomputed for it.
     *
     * Returns: ['headline' => string, 'lines' => string[],
     *           'completion' => ?string, 'action' => ?['label' => string]]
     */
    private function resolveTodayCardState(
        ?EmployeeAttendance $attendance,
        ?string $markableOtherHalf,
        bool $todayIsNonWorking,
        User $user,
        Carbon $today,
    ): array {
        $halfDayFamily = ['half_day', 'half_day_leave', 'half_day_lop'];
        $periodLabel   = fn (string $period) => $period === 'first_half' ? 'First Half' : 'Second Half';

        // State 1 — nothing marked at all.
        if ($attendance === null) {
            return [
                'headline'   => 'Attendance',
                'lines'      => ['Not marked'],
                'completion' => null,
                'action'     => $todayIsNonWorking ? null : ['label' => 'Mark Attendance'],
            ];
        }

        // State 4 — a plain full-day present mark.
        if ($attendance->status === 'present') {
            return [
                'headline'   => 'Attendance',
                'lines'      => ['✓ Full Day'],
                'completion' => null,
                'action'     => null,
            ];
        }

        if (in_array($attendance->status, $halfDayFamily, true) && $attendance->half_day_period !== null) {
            $period      = $attendance->half_day_period;
            $otherPeriod = $period === 'first_half' ? 'second_half' : 'first_half';
            $ownIsLeave  = in_array($attendance->status, ['half_day_leave', 'half_day_lop'], true);
            $ownLabel    = $ownIsLeave
                ? ($attendance->leaveRequest?->leaveType?->name ?? ($attendance->status === 'half_day_lop' ? 'Loss of Pay' : 'Leave'))
                : 'Attendance';

            $segment = EmployeeAttendanceSegment::where('user_id', $user->id)
                ->whereDate('attendance_date', $today->toDateString())
                ->where('period', $otherPeriod)
                ->with('leaveRequest.leaveType')
                ->first();

            if ($segment === null) {
                if ($ownIsLeave) {
                    // State 7 — half-day leave only, opposite half genuinely
                    // unmarked. Only ever offer the action when the service
                    // itself says the other half is still markable.
                    return [
                        'headline'   => 'Attendance & Leave',
                        'lines'      => ["✓ {$periodLabel($period)} · {$ownLabel}"],
                        'completion' => null,
                        'action'     => $markableOtherHalf ? ['label' => 'Mark '.$periodLabel($otherPeriod)] : null,
                    ];
                }

                // States 2/3 — one half marked present, other half free.
                return [
                    'headline'   => 'Attendance',
                    'lines'      => ["✓ {$periodLabel($period)} marked"],
                    'completion' => null,
                    'action'     => $markableOtherHalf ? ['label' => 'Mark '.$periodLabel($otherPeriod)] : null,
                ];
            }

            $otherIsLeave = in_array($segment->status, ['leave', 'lop'], true);
            $otherLabel   = $otherIsLeave
                ? ($segment->leaveRequest?->leaveType?->name ?? ($segment->status === 'lop' ? 'Loss of Pay' : 'Leave'))
                : 'Attendance';

            if ($ownIsLeave || $otherIsLeave) {
                // States 5/6 — one half attendance, one half leave.
                return [
                    'headline'   => 'Attendance & Leave',
                    'lines'      => [
                        "✓ {$periodLabel($period)} · {$ownLabel}",
                        "✓ {$periodLabel($otherPeriod)} · {$otherLabel}",
                    ],
                    'completion' => 'Day completed',
                    'action'     => null,
                ];
            }

            // Both halves independently marked present -> a completed full day.
            return [
                'headline'   => 'Attendance',
                'lines'      => ['✓ Full Day'],
                'completion' => null,
                'action'     => null,
            ];
        }

        // Full-day leave/LOP/absent — outside the 7 enumerated states, but
        // still needs a sane, non-crashing rendering.
        $label = $attendance->leaveRequest?->leaveType?->name ?? ucfirst(str_replace('_', ' ', $attendance->status));

        return [
            'headline'   => 'Attendance',
            'lines'      => ["✓ {$label}"],
            'completion' => null,
            'action'     => null,
        ];
    }
}
