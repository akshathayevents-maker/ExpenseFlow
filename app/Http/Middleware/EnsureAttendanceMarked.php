<?php

namespace App\Http\Middleware;

use App\Services\EmployeeAttendanceService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Attendance-first login gate — employee role only. Managers/admins are
 * never subject to this (they have no employee attendance workflow).
 *
 * Never re-derives holiday/weekly-off/leave rules itself — asks
 * EmployeeAttendanceService::needsAttendanceToday() (which itself reuses
 * isTodayNonWorking()/hasApprovedLeave()/getToday(), the same pieces
 * getAttendanceDayState() and assertRegularizable() already use).
 *
 * employee.leave.* is also exempt: an employee must be able to open Leave
 * and apply even before marking today's attendance (e.g. applying for leave
 * that starts today, before attendance would even be relevant).
 *
 * Applied as route middleware (not a one-time login redirect) so it also
 * catches direct navigation to any protected employee page later in the
 * session, and cannot be bypassed by redirect()->intended().
 */
class EnsureAttendanceMarked
{
    public function __construct(private EmployeeAttendanceService $service) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user
            && $user->isEmployee()
            && ! $request->routeIs('employee.attendance.*', 'employee.attendance-regularizations.*', 'employee.leave.*')
            && $this->service->needsAttendanceToday($user)
        ) {
            // AJAX/JSON callers (e.g. the hall calendar's calendar-events
            // fetch) must not receive an HTML redirect — that would look
            // like a malformed response to the caller. Give them a JSON
            // error they can actually handle; only a normal browser
            // navigation gets sent to the attendance page.
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Attendance for today has not been marked yet.',
                    'redirect' => route('employee.attendance.index'),
                ], 409);
            }

            return redirect()->route('employee.attendance.index');
        }

        return $next($request);
    }
}
