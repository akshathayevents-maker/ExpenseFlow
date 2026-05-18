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

    public function index(Request $request): View
    {
        // Ensure every employee/manager has a wallet
        User::whereIn('role', ['employee', 'manager'])
            ->doesntHave('wallet')
            ->get()
            ->each(fn ($u) => $this->walletService->getOrCreate($u));

        $search = $request->get('search', '');
        $health = $request->get('health', '');

        $wallets = Wallet::with(['user'])
            ->whereHas('user', fn ($q) => $q
                ->whereIn('role', ['employee', 'manager'])
                ->when($search, fn ($q) => $q->where(fn ($q) => $q
                    ->where('name',  'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                ))
            )
            ->when($health === 'low',      fn ($q) => $q->where('balance', '<', 500)->where('balance', '>=', 0))
            ->when($health === 'critical', fn ($q) => $q->where('balance', '<', 0))
            ->when($health === 'healthy',  fn ($q) => $q->where('balance', '>=', 500))
            ->orderByRaw("CASE WHEN balance < 0 THEN 0 WHEN balance < 500 THEN 1 ELSE 2 END")
            ->orderBy('balance')
            ->paginate(24)
            ->withQueryString();

        $baseScope = Wallet::whereHas('user', fn ($q) => $q->whereIn('role', ['employee', 'manager']));

        $agg = (clone $baseScope)
            ->selectRaw('COALESCE(SUM(balance), 0) as total_balance, COUNT(*) as total_wallets, COALESCE(AVG(balance), 0) as avg_balance')
            ->first();

        $stats = [
            'total_balance'       => (float) ($agg->total_balance   ?? 0),
            'total_wallets'       => (int)   ($agg->total_wallets   ?? 0),
            'avg_balance'         => (float) ($agg->avg_balance     ?? 0),
            'low_balance_count'   => (clone $baseScope)->where('balance', '<', 500)->where('balance', '>=', 0)->count(),
            'negative_count'      => (clone $baseScope)->where('balance', '<', 0)->count(),
            'pending_reimb_count' => ExpenseRequest::reimbursementPending()->count(),
        ];

        return view('admin.wallets.index', compact('wallets', 'stats', 'search', 'health'));
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

        $base = WalletTransaction::where('wallet_id', $wallet->id);

        $stats = [
            'total_credited' => (float) (clone $base)->whereIn('type', ['credit', 'reimbursement'])->sum('amount'),
            'total_debited'  => (float) (clone $base)->where('type', 'debit')->sum('amount'),
            'txn_count'      => (clone $base)->count(),
            'last_txn_at'    => (clone $base)->latest()->value('created_at'),
        ];

        return view('admin.wallets.show', compact('wallet', 'transactions', 'stats'));
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
