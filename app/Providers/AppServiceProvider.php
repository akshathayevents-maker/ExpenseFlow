<?php

namespace App\Providers;

use App\Models\EventRequest;
use App\Models\ExpenseRequest;
use App\Models\MaterialRequest;
use App\Models\Purchase;
use App\Policies\MaterialRequestPolicy;
use App\Policies\PurchasePolicy;
use App\Policies\EventRequestPolicy;
use App\Policies\ExpenseRequestPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::policy(ExpenseRequest::class, ExpenseRequestPolicy::class);
        Gate::policy(EventRequest::class, EventRequestPolicy::class);
        Gate::policy(Purchase::class, PurchasePolicy::class);
        Gate::policy(MaterialRequest::class, MaterialRequestPolicy::class);
    }
}
