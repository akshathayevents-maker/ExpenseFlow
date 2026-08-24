<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\EmployeeAttendanceService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(private EmployeeAttendanceService $service) {}

    public function index(Request $request): View
    {
        $user = auth()->user();

        $currentMonthStart = $this->service->today()->copy()->startOfMonth();
        $monthStart = $currentMonthStart->copy();

        // ?month=YYYY-MM — never trust the client for anything beyond which
        // month to *display*; a future month simply clamps back to the
        // current month, it never fabricates future attendance data.
        if ($request->filled('month')) {
            try {
                $requested = Carbon::createFromFormat('Y-m', $request->string('month'))->startOfMonth();
                if ($requested->lte($currentMonthStart)) {
                    $monthStart = $requested;
                }
            } catch (\Exception) {
                // malformed input — silently fall back to the current month
            }
        }

        $today   = $this->service->getToday($user);
        $summary = $this->service->getMonthlySummary($user, $monthStart);
        $history = $this->service->getMonthlyHistory($user, $monthStart);
        $regularizations = $this->service->listRegularizationsForEmployee($user);

        // ?date=YYYY-MM-DD — the date-selection card on this page. Never
        // trust it beyond "which date to display": a malformed or future
        // date silently falls back to today, same clamping style as ?month.
        $selectedDate = $this->service->today();
        if ($request->filled('date')) {
            try {
                $requestedDate = Carbon::createFromFormat('Y-m-d', $request->string('date'))->startOfDay();
                if ($requestedDate->lte($this->service->today())) {
                    $selectedDate = $requestedDate;
                }
            } catch (\Exception) {
                // malformed input — silently fall back to today
            }
        }
        $dayState = $this->service->getAttendanceDayState($user, $selectedDate);

        return view('employee.attendance.index', [
            'regularizations' => $regularizations,
            'selectedDate' => $selectedDate,
            'dayState'     => $dayState,
            'today'      => $today,
            'todayDate'  => $this->service->today(),
            'todayIsNonWorking' => $this->service->isTodayNonWorking(),
            'todayCategory'     => $this->service->todayCategory(),
            'summary'    => $summary,
            'history'    => $history,
            'monthStart' => $monthStart,
            'isCurrentMonth' => $monthStart->isSameMonth($currentMonthStart),
            'prevMonth'  => $monthStart->copy()->subMonth()->format('Y-m'),
            'nextMonth'  => $monthStart->copy()->addMonth()->format('Y-m'),
            'canGoNext'  => $monthStart->lt($currentMonthStart),
        ]);
    }

    public function markPresent(): RedirectResponse
    {
        $this->service->markPresent(auth()->user());

        return back()->with('success', 'Attendance marked as Present.');
    }

    public function markHalfDay(): RedirectResponse
    {
        $this->service->markHalfDay(auth()->user());

        return back()->with('success', 'Attendance marked as Half Day.');
    }
}
