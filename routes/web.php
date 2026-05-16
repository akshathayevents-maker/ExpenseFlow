<?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DailyClosingController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\ExpenseRequestController as AdminExpenseRequestController;
use App\Http\Controllers\Admin\Inventory\InventoryCategoryController;
use App\Http\Controllers\Admin\Inventory\InventoryItemController;
use App\Http\Controllers\Admin\Inventory\StockAlertController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PurchasePlanController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\Admin\WalletController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;
use App\Http\Controllers\Employee\ExpenseRequestController as EmployeeExpenseRequestController;
use App\Http\Controllers\Employee\WalletController as EmployeeWalletController;
use App\Http\Controllers\Manager\DashboardController as ManagerDashboardController;
use App\Http\Controllers\Manager\ExpenseRequestController as ManagerExpenseRequestController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        $role = auth()->user()->role ?? 'employee';
        return redirect()->route($role . '.dashboard');
    }
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    $role = auth()->user()->role ?? 'employee';
    return redirect()->route($role . '.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// ── Admin ─────────────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'role.admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Employees
    Route::resource('employees', EmployeeController::class);
    Route::patch('employees/{employee}/toggle-status', [EmployeeController::class, 'toggleStatus'])
        ->name('employees.toggle-status');

    // Categories
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::patch('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])
        ->name('categories.toggle-status');

    // Vendors
    Route::resource('vendors', VendorController::class)->except(['show']);
    Route::patch('vendors/{vendor}/toggle-status', [VendorController::class, 'toggleStatus'])
        ->name('vendors.toggle-status');

    // Expense Requests
    Route::get('expense-requests', [AdminExpenseRequestController::class, 'index'])->name('expense-requests.index');
    Route::get('expense-requests/{expenseRequest}', [AdminExpenseRequestController::class, 'show'])->name('expense-requests.show');
    Route::patch('expense-requests/{expenseRequest}/approve', [AdminExpenseRequestController::class, 'approve'])->name('expense-requests.approve');
    Route::patch('expense-requests/{expenseRequest}/reject', [AdminExpenseRequestController::class, 'reject'])->name('expense-requests.reject');
    Route::patch('expense-requests/{expenseRequest}/settle-wallet', [AdminExpenseRequestController::class, 'settleViaWallet'])->name('expense-requests.settle-wallet');
    Route::patch('expense-requests/{expenseRequest}/settle-direct', [AdminExpenseRequestController::class, 'settleViaDirect'])->name('expense-requests.settle-direct');
    Route::patch('expense-requests/{expenseRequest}/reimbursement-pending', [AdminExpenseRequestController::class, 'markReimbursementPending'])->name('expense-requests.reimbursement-pending');
    Route::patch('expense-requests/{expenseRequest}/reimburse', [AdminExpenseRequestController::class, 'reimburse'])->name('expense-requests.reimburse');
    Route::patch('expense-requests/{expenseRequest}/mark-completed', [AdminExpenseRequestController::class, 'markCompleted'])->name('expense-requests.mark-completed');
    Route::delete('expense-requests/{expenseRequest}', [AdminExpenseRequestController::class, 'destroy'])->name('expense-requests.destroy');

    // Wallets
    Route::get('wallets', [WalletController::class, 'index'])->name('wallets.index');
    Route::get('wallets/{user}', [WalletController::class, 'show'])->name('wallets.show');
    Route::post('wallets/{user}/transact', [WalletController::class, 'transact'])->name('wallets.transact');

    // Payments
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');

    // Reports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/employee', [ReportController::class, 'employee'])->name('reports.employee');
    Route::get('reports/category', [ReportController::class, 'category'])->name('reports.category');
    Route::get('reports/vendor', [ReportController::class, 'vendor'])->name('reports.vendor');
    Route::get('reports/ledger', [ReportController::class, 'ledger'])->name('reports.ledger');
    Route::get('reports/reimbursement', [ReportController::class, 'reimbursement'])->name('reports.reimbursement');
    Route::get('reports/daily', [ReportController::class, 'daily'])->name('reports.daily');
    Route::get('reports/monthly', [ReportController::class, 'monthly'])->name('reports.monthly');

    // ── Inventory ─────────────────────────────────────────────────────────────
    Route::prefix('inventory')->name('inventory.')->group(function () {
        // Categories
        Route::resource('categories', InventoryCategoryController::class)->except(['show']);
        Route::patch('categories/{category}/toggle-status', [InventoryCategoryController::class, 'toggleStatus'])
            ->name('categories.toggle-status');

        // Items + stock transactions
        Route::get('items', [InventoryItemController::class, 'index'])->name('items.index');
        Route::get('items/create', [InventoryItemController::class, 'create'])->name('items.create');
        Route::post('items', [InventoryItemController::class, 'store'])->name('items.store');
        Route::get('items/{item}', [InventoryItemController::class, 'show'])->name('items.show');
        Route::get('items/{item}/edit', [InventoryItemController::class, 'edit'])->name('items.edit');
        Route::put('items/{item}', [InventoryItemController::class, 'update'])->name('items.update');
        Route::patch('items/{item}/toggle-status', [InventoryItemController::class, 'toggleStatus'])->name('items.toggle-status');
        Route::post('items/{item}/transact', [InventoryItemController::class, 'transact'])->name('items.transact');

        // Stock alerts
        Route::get('alerts', [StockAlertController::class, 'index'])->name('alerts.index');
        Route::patch('alerts/{alert}/resolve', [StockAlertController::class, 'resolve'])->name('alerts.resolve');
        Route::patch('alerts/resolve-all', [StockAlertController::class, 'resolveAll'])->name('alerts.resolve-all');
    });

    // Purchase Plans
    Route::get('purchase-suggestions', [PurchasePlanController::class, 'suggestions'])->name('purchase-plans.suggestions');
    Route::get('purchase-plans', [PurchasePlanController::class, 'index'])->name('purchase-plans.index');
    Route::get('purchase-plans/create', [PurchasePlanController::class, 'create'])->name('purchase-plans.create');
    Route::post('purchase-plans', [PurchasePlanController::class, 'store'])->name('purchase-plans.store');
    Route::get('purchase-plans/{purchasePlan}', [PurchasePlanController::class, 'show'])->name('purchase-plans.show');
    Route::patch('purchase-plans/{purchasePlan}/approve', [PurchasePlanController::class, 'approve'])->name('purchase-plans.approve');
    Route::patch('purchase-plans/{purchasePlan}/status', [PurchasePlanController::class, 'updateStatus'])->name('purchase-plans.status');

    // Analytics
    Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('analytics/inventory', [AnalyticsController::class, 'inventory'])->name('analytics.inventory');

    // Audit Logs
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    // Daily Closing
    Route::get('daily-closings', [DailyClosingController::class, 'index'])->name('daily-closings.index');
    Route::get('daily-closings/create', [DailyClosingController::class, 'create'])->name('daily-closings.create');
    Route::post('daily-closings', [DailyClosingController::class, 'store'])->name('daily-closings.store');
    Route::get('daily-closings/{dailyClosing}', [DailyClosingController::class, 'show'])->name('daily-closings.show');
    Route::patch('daily-closings/{dailyClosing}/verify', [DailyClosingController::class, 'verify'])->name('daily-closings.verify');

    // Settings
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
});

// ── Manager ───────────────────────────────────────────────────────────────────
Route::prefix('manager')->name('manager.')->middleware(['auth', 'verified', 'role.manager'])->group(function () {
    Route::get('/dashboard', [ManagerDashboardController::class, 'index'])->name('dashboard');

    Route::get('expense-requests', [ManagerExpenseRequestController::class, 'index'])->name('expense-requests.index');
    Route::get('expense-requests/{expenseRequest}', [ManagerExpenseRequestController::class, 'show'])->name('expense-requests.show');
    Route::patch('expense-requests/{expenseRequest}/approve', [ManagerExpenseRequestController::class, 'approve'])->name('expense-requests.approve');
    Route::patch('expense-requests/{expenseRequest}/reject', [ManagerExpenseRequestController::class, 'reject'])->name('expense-requests.reject');
});

// ── Employee ──────────────────────────────────────────────────────────────────
Route::prefix('employee')->name('employee.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');

    Route::get('expense-requests', [EmployeeExpenseRequestController::class, 'index'])->name('expense-requests.index');
    Route::get('expense-requests/create', [EmployeeExpenseRequestController::class, 'create'])->name('expense-requests.create');
    Route::post('expense-requests', [EmployeeExpenseRequestController::class, 'store'])->name('expense-requests.store');
    Route::get('expense-requests/{expenseRequest}', [EmployeeExpenseRequestController::class, 'show'])->name('expense-requests.show');

    Route::get('wallet', [EmployeeWalletController::class, 'show'])->name('wallet.show');
});

// ── Notifications (all authenticated users) ───────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::get('notifications/count', [NotificationController::class, 'unreadCount'])->name('notifications.count');
});

// ── Profile ───────────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
