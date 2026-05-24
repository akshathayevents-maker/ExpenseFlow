<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * Supports an `?intended=` query param on the login GET page so the
     * payment-request "Login as Staff" button can pre-set the return URL
     * without relying on the Authenticate middleware (which is not applied
     * to the public payment route).  The /pay-login helper route stores the
     * value in `url.intended` before arriving here; this is the fallback for
     * any consumer that passes the raw ?intended= param directly.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = auth()->user();

        $destination = match ($user->role) {
            'admin', 'manager' => route('hall.bookings.calendar'),
            default            => route('employee.expense-requests.create'),
        };

        return redirect()->intended($destination);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
