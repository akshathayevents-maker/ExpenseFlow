<?php

use App\Http\Controllers\Admin\EventRequest\EventRequestController;
use App\Http\Controllers\Admin\EventRequest\MenuCategoryController;
use App\Http\Controllers\Admin\EventRequest\MenuItemController;
use App\Http\Controllers\EventRequest\PublicEventRequestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Event Request Portal
|--------------------------------------------------------------------------
| Self-contained routes for the new Event Request Portal module. Kept in
| its own file and simply required from routes/web.php so none of the
| existing route groups need to be touched.
*/

// ── Public (no auth — access is gated by the secure token) ─────────────────
Route::prefix('event-request')->name('event-request.public.')->group(function () {
    Route::get('/{token}', [PublicEventRequestController::class, 'show'])->name('show');
    Route::post('/{token}', [PublicEventRequestController::class, 'submit'])->name('submit');
});

// ── Admin (same access level as the rest of the Hall Management module) ───
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'role.hall'])->group(function () {
    Route::prefix('event-requests')->name('event-requests.')->group(function () {
        Route::get('/', [EventRequestController::class, 'index'])->name('index');
        Route::get('/create', [EventRequestController::class, 'create'])->name('create');
        Route::post('/', [EventRequestController::class, 'store'])->name('store');
        Route::get('/{event_request}', [EventRequestController::class, 'show'])->name('show');
        Route::put('/{event_request}', [EventRequestController::class, 'update'])->name('update');
        Route::post('/{event_request}/approve', [EventRequestController::class, 'approve'])->name('approve');
        Route::post('/{event_request}/reject', [EventRequestController::class, 'reject'])->name('reject');
        Route::post('/{event_request}/need-changes', [EventRequestController::class, 'needChanges'])->name('need-changes');
        Route::post('/{event_request}/regenerate-link', [EventRequestController::class, 'regenerateLink'])->name('regenerate-link');
        Route::post('/{event_request}/deactivate-link', [EventRequestController::class, 'deactivateLink'])->name('deactivate-link');
    });

    Route::prefix('event-request-menu')->name('event-request-menu.')->group(function () {
        Route::get('categories', [MenuCategoryController::class, 'index'])->name('categories.index');
        Route::post('categories', [MenuCategoryController::class, 'store'])->name('categories.store');
        Route::put('categories/{category}', [MenuCategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{category}', [MenuCategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('items', [MenuItemController::class, 'index'])->name('items.index');
        Route::post('items', [MenuItemController::class, 'store'])->name('items.store');
        Route::put('items/{item}', [MenuItemController::class, 'update'])->name('items.update');
        Route::delete('items/{item}', [MenuItemController::class, 'destroy'])->name('items.destroy');
    });
});
