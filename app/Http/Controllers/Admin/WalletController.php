<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\WalletTransactionRequest;
use App\Models\ExpenseRequest;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function __construct(private WalletService $walletService) {}

    public function index(): View
    {
        // Ensure every employee/manager has a wallet
        User::whereIn('role', ['employee', 'manager'])
            ->doesntHave('wallet')
            ->get()
            ->each(fn ($u) => $this->walletService->getOrCreate($u));

        $wallets = Wallet::with('user')
            ->whereHas('user', fn ($q) => $q->whereIn('role', ['employee', 'manager']))
            ->orderByDesc('updated_at')
            ->paginate(20);

        $totalBalance     = Wallet::sum('balance');
        $lowBalanceCount  = Wallet::where('balance', '<', 500)->count();
        $pendingReimbCount = ExpenseRequest::reimbursementPending()->count();

        return view('admin.wallets.index', compact('wallets', 'totalBalance', 'lowBalanceCount', 'pendingReimbCount'));
    }

    public function show(User $user, Request $request): View
    {
        $wallet = $this->walletService->getOrCreate($user);

        $transactions = WalletTransaction::where('wallet_id', $wallet->id)
            ->with(['expenseRequest', 'creator'])
            ->when($request->get('type'), fn ($q, $v) => $q->where('type', $v))
            ->when($request->get('from'), fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($request->get('to'), fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.wallets.show', compact('wallet', 'transactions'));
    }

    public function transact(WalletTransactionRequest $request, User $user): RedirectResponse|JsonResponse
    {
        $wallet = $this->walletService->getOrCreate($user);
        $data   = $request->validated();

        try {
            match ($data['type']) {
                'credit'     => $this->walletService->credit($wallet, $data['amount'], $data['notes'], auth()->user()),
                'debit'      => $this->walletService->debit($wallet, $data['amount'], $data['notes'], auth()->user()),
                'adjustment' => $this->walletService->adjust($wallet, $data['amount'], $data['notes'], auth()->user()),
            };

            $wallet->refresh();
            $label   = ucfirst($data['type']);
            $message = "{$label} of ₹" . number_format((float) $data['amount'], 2) . " recorded successfully.";

            if ($request->wantsJson()) {
                return response()->json([
                    'balance' => (float) $wallet->balance,
                    'message' => $message,
                ]);
            }

            return back()->with('success', $message);
        } catch (\RuntimeException $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }
}
