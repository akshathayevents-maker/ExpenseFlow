<?php

namespace App\Providers;

use App\Models\ExpenseRequest;
use App\Policies\ExpenseRequestPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::policy(ExpenseRequest::class, ExpenseRequestPolicy::class);
    }
}
