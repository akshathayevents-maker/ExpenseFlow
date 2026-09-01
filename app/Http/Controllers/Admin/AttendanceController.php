<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployeeAttendanceRegularization;
use App\Models\User;
use App\Services\EmployeeAttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Read-only admin view over EXISTING attendance data (EmployeeAttendance,
 * EmployeeAttendanceRegularization). No new attendance/regularization
 * business logic is introduced here — every per-day/per-month classification
 * is delegated to EmployeeAttendanceService, the same service the
 * employee-facing attendance page already uses, so the admin view can never
 * show a status the employee-facing page wouldn't also show for the same
 * data. Approval itself still happens only on the existing
 * admin.attendance-regularizations.* routes/view.
 */
class AttendanceController extends Controller
{
    public function __construct(
        private EmployeeAttendanceService $service,
    ) {}

    public function index(Request $request): View
    {
        $month = $this->resolveMonth($request->query('month'));
        $today = $this->service->today();

        $employees = User::whereIn('role', ['employee', 'manager'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $pendingRegularizationUserIds = EmployeeAttendanceRegularization::where('request_status', 'pending')
            ->pluck('user_id')
            ->flip();

        $todayRows = $employees->map(function (User $employee) use ($today, $pendingRegularizationUserIds) {
            $state = $this->service->getAttendanceDayState($employee, $today);

            [$status, $firstHalf, $secondHalf] = $this->classifyDay($state);

            return [
                'employee'    => $employee,
                'status'      => $status,
                'first_half'  => $firstHalf,
                'second_half' => $secondHalf,
                'has_pending_regularization' => isset($pendingRegularizationUserIds[$employee->id]),
            ];
        });

        $todaySummary = [
            'present'    => $todayRows->whereIn('status', ['present', 'half_day'])->count(),
            'absent'     => $todayRows->where('status', 'absent')->count(),
            'on_leave'   => $todayRows->whereIn('status', ['leave', 'half_day_leave'])->count(),
            'not_marked' => $todayRows->where('status', 'not_marked')->count(),
        ];

        $monthRows = $employees->map(function (User $employee) use ($month) {
            $summary = $this->service->getMonthlySummary($employee, $month->copy());

            $regularizationCount = EmployeeAttendanceRegularization::where('user_id', $employee->id)
                ->whereDate('attendance_date', '>=', $month->toDateString())
                ->whereDate('attendance_date', '<=', $month->copy()->endOfMonth()->toDateString())
                ->count();

            return [
                'employee' => $employee,
                'summary'  => $summary,
                'regularizations' => $regularizationCount,
            ];
        });

        $prevMonth = $month->copy()->subMonth()->format('Y-m');
        $nextMonth = $month->copy()->addMonth()->format('Y-m');

        return view('admin.attendance.index', [
            'today' => $today,
            'todayRows' => $todayRows,
            'todaySummary' => $todaySummary,
            'monthRows' => $monthRows,
            'month' => $month,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
        ]);
    }

    public function show(Request $request, User $employee): View
    {
        $month = $this->resolveMonth($request->query('month'));

        $history = $this->service->getMonthlyHistory($employee, $month->copy());
        $summary = $this->service->getMonthlySummary($employee, $month->copy());

        $prevMonth = $month->copy()->subMonth()->format('Y-m');
        $nextMonth = $month->copy()->addMonth()->format('Y-m');

        return view('admin.attendance.show', [
            'employee' => $employee,
            'history' => $history,
            'summary' => $summary,
            'month' => $month,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
        ]);
    }

    /**
     * Derives (status, first_half_label, second_half_label) from
     * EmployeeAttendanceService::getAttendanceDayState()'s output — never
     * re-derives holiday/weekly-off/leave precedence itself.
     *
     * @return array{0: string, 1: string, 2: string}
     */
    private function classifyDay(array $state): array
    {
        $attendance = $state['attendance'];
        $approvedLeave = $state['approved_leave'];

        if ($attendance !== null) {
            $status = $attendance->status;
            $isHalfDayFamily = in_array($status, ['half_day', 'half_day_leave', 'half_day_lop'], true);

            if (! $isHalfDayFamily) {
                $label = $this->statusLabel($status);
                return [$status, $label, $label];
            }

            $label = $this->statusLabel($status);
            $first = $attendance->half_day_period === 'first_half' ? $label : '—';
            $second = $attendance->half_day_period === 'second_half' ? $label : '—';

            return [$status, $first, $second];
        }

        if ($approvedLeave !== null) {
            $status = $approvedLeave->is_half_day ? 'half_day_leave' : 'leave';
            $label = $this->statusLabel($status);

            if (! $approvedLeave->is_half_day) {
                return [$status, $label, $label];
            }

            $first = $approvedLeave->half_day_period === 'first_half' ? $label : '—';
            $second = $approvedLeave->half_day_period === 'second_half' ? $label : '—';

            return [$status, $first, $second];
        }

        if ($state['category'] === 'holiday') {
            return ['holiday', 'Holiday', 'Holiday'];
        }

        if ($state['category'] === 'weekend') {
            return ['weekly_off', 'Weekly Off', 'Weekly Off'];
        }

        return ['not_marked', 'Not Marked', 'Not Marked'];
    }

    private function statusLabel(string $status): string
    {
        return ucfirst(str_replace('_', ' ', $status));
    }

    /**
     * Resolve the selected month from a raw "Y-m" query value — identical
     * validation/default-to-current-month convention to
     * Hall\HallDashboardController::resolveMonth().
     */
    private function resolveMonth(?string $raw): Carbon
    {
        if ($raw && preg_match('/^(\d{4})-(0[1-9]|1[0-2])$/', $raw, $m)) {
            try {
                return Carbon::create((int) $m[1], (int) $m[2], 1)->startOfMonth();
            } catch (\Throwable) {
                // fall through to default
            }
        }

        return today()->startOfMonth();
    }
}
