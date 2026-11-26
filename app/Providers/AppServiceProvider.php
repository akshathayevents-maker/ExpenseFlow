<?php

namespace App\Providers;

use App\Models\EventRequest;
use App\Models\ExpenseRequest;
use App\Models\MaterialRequest;
use App\Models\Purchase;
use App\Policies\EventRequestPolicy;
use App\Policies\ExpenseRequestPolicy;
use App\Policies\MaterialRequestPolicy;
use App\Policies\PurchasePolicy;
use App\Services\Ai\ClaudeRecipeAiClient;
use App\Services\Ai\RecipeAiClient;
use App\Services\QueueHealth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // AI recipe generation: tests bind a fake; production uses Claude via the official SDK.
        $this->app->bind(RecipeAiClient::class, ClaudeRecipeAiClient::class);
    }

    public function boot(): void
    {
        Gate::policy(ExpenseRequest::class, ExpenseRequestPolicy::class);
        // Recipe AI generation, verification and archiving: administrators only (managers can still edit recipes,
        // which sends a verified recipe back to review).
        Gate::define('recipes.generate', fn ($user) => $user->isAdmin());
        // heartbeat so the recipe generator can tell whether a queue worker is running
        Queue::looping(fn () => QueueHealth::beat());
        Gate::define('recipes.verify', fn ($user) => $user->isAdmin());
        Gate::policy(EventRequest::class, EventRequestPolicy::class);
        Gate::policy(Purchase::class, PurchasePolicy::class);
        Gate::policy(MaterialRequest::class, MaterialRequestPolicy::class);
    }
}
