<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function __construct(private WalletService $walletService) {}

    public function show(Request $request): View
    {
        $user   = auth()->user();
        $wallet = $this->walletService->getOrCreate($user);

        $transactions = WalletTransaction::where('wallet_id', $wallet->id)
            ->with(['expenseRequest', 'creator'])
            ->when($request->get('type'), fn ($q, $v) => $q->where('type', $v))
            ->when($request->get('from'), fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($request->get('to'), fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('employee.wallet.show', compact('wallet', 'transactions'));
    }
}
