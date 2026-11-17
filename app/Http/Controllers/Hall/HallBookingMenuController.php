<?php

namespace App\Http\Controllers\Hall;

use App\Http\Controllers\Controller;
use App\Models\HallBooking;
use App\Services\Inventory\HallMenuService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Final (customised) menu of a hall order — route group role.hall (admin + manager). */
class HallBookingMenuController extends Controller
{
    public function __construct(private HallMenuService $menus) {}

    public function edit(HallBooking $booking): View
    {
        $booking->load('mealPlan', 'foodSplits');

        return view('hall.bookings.menu', ['booking' => $booking, 'lines' => old('menu', $this->menus->draftLines($booking))]);
    }

    public function update(Request $request, HallBooking $booking): RedirectResponse
    {
        $data = $request->validate([
            'menu' => ['nullable', 'array', 'max:150'],
            'menu.*.name' => ['nullable', 'string', 'max:150'],
            'menu.*.meal_type' => ['nullable', 'string', 'max:20'],
            'menu.*.change_type' => ['nullable', 'in:plan,added,modified,removed'],
            'menu.*.notes' => ['nullable', 'string', 'max:300'],
        ]);
        $stale = $this->menus->saveMenu($booking, $data['menu'] ?? [], $request->user());

        $msg = 'Final menu saved.';
        if ($stale) {
            $refs = implode(', ', array_map(fn ($r) => $r->reference(), $stale));
            $msg .= " Material request(s) {$refs} were made for the old menu — they were NOT changed; create a revision if quantities must change.";
        }

        return redirect()->route('hall.bookings.show', $booking)->with($stale ? 'error' : 'success', $msg);
    }
}
