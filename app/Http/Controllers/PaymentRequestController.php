<?php

namespace App\Http\Controllers;

use App\Models\ExpenseRequest;
use Illuminate\View\View;

class PaymentRequestController extends Controller
{
    public function show(int $id): View
    {
        $expense = ExpenseRequest::with('requester')->findOrFail($id);

        return view('payment-request.show', compact('expense'));
    }
}
