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
        $markableOtherHalf = $this->service->markableOtherHalfToday($user);
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
            // Session-flashed by EnsureAttendanceMarked ONLY on the request
            // that immediately follows a gate redirect — true here means
            // "the employee just got sent here because they tried to reach
            // a gated page." A plain, direct visit to Attendance never sets
            // this, so the banner never shows on an ordinary visit.
            'attendanceGateTriggered' => (bool) session('attendance_gate_triggered', false),
            'regularizations' => $regularizations,
            'selectedDate' => $selectedDate,
            'dayState'     => $dayState,
            'today'      => $today,
            'markableOtherHalf' => $markableOtherHalf,
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

    public function markPresent(Request $request): RedirectResponse
    {
        $period = $this->validatedPeriod($request);

        $this->service->markPresent(auth()->user(), $period);

        return $this->redirectAfterMarking('Attendance marked as Present.');
    }

    public function markHalfDay(Request $request): RedirectResponse
    {
        // Unlike markPresent() (where half_day_period is always optional —
        // a plain full-day "Present" never needs one, and the "mark the
        // other half present" flow supplies it via a hidden field derived
        // server-side from markableOtherHalfToday(), never from open user
        // choice), a half-day mark is inherently ambiguous without a period
        // and must be rejected here with a clear, visible validation error
        // — mirroring the required_if pattern already used by
        // StoreAttendanceRegularizationRequest — rather than silently
        // falling through to the service layer's own guard.
        $request->validate([
            'half_day_period' => ['required', 'in:first_half,second_half'],
        ]);

        $period = $this->validatedPeriod($request);

        $this->service->markHalfDay(auth()->user(), $period);

        return $this->redirectAfterMarking('Attendance marked as Half Day.');
    }

    /**
     * Optional `half_day_period` form field, accepted on both mark-present
     * and mark-half-day submissions — always optional (a plain full-day
     * mark never sends it), validated to one of the two known period
     * values when present. The service layer, not this validation, is
     * what actually decides whether a period is usable for today (e.g.
     * rejecting it outright if the day has no complementary occupancy at
     * all) — this only guards against a malformed/unexpected value.
     */
    private function validatedPeriod(Request $request): ?string
    {
        if (! $request->filled('half_day_period')) {
            return null;
        }

        $period = $request->string('half_day_period')->toString();

        return in_array($period, ['first_half', 'second_half'], true) ? $period : null;
    }

    /**
     * After successfully marking today's attendance, send the employee back
     * to whatever gated page they originally tried to reach (stored by
     * EnsureAttendanceMarked in 'attendance_gate_return_to'), not just
     * back() to this same page — that's what makes "Request Overtime" ->
     * (mark attendance) -> land back on the overtime form actually work.
     * pull() reads AND clears in one step, so the redirect only ever fires
     * once per gate trip.
     *
     * The stored value is never trusted blindly: it was written by our own
     * middleware from $request->fullUrl() on THIS app, but the value only
     * ever gets used if it still resolves to a URL on this same host and
     * under /employee — belt-and-braces against ever open-redirecting even
     * though the write path is not user-controlled.
     */
    private function redirectAfterMarking(string $message): RedirectResponse
    {
        $intended = session()->pull('attendance_gate_return_to');

        if (is_string($intended)) {
            $path = parse_url($intended, PHP_URL_PATH) ?? '';
            $sameHost = parse_url($intended, PHP_URL_HOST) === parse_url(url('/'), PHP_URL_HOST);

            if ($sameHost && str_starts_with($path, '/employee/') && ! str_starts_with($path, '/employee/attendance')) {
                return redirect()->to($intended)->with('success', $message);
            }
        }

        return back()->with('success', $message);
    }
}
