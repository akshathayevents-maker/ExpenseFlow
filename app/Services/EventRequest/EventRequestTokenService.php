<?php

namespace App\Services\EventRequest;

use App\Models\EventRequest;
use App\Models\EventRequestPublicToken;
use Illuminate\Support\Str;

/**
 * Issues and revokes the secure public links clients use to reach their
 * Event Request. Tokens are opaque, unguessable, and revocable independently
 * of the request itself so "regenerate link" never touches request data.
 */
class EventRequestTokenService
{
    public function issue(EventRequest $eventRequest, ?int $expiresInDays = null): EventRequestPublicToken
    {
        return $eventRequest->tokens()->create([
            'token'      => $this->generateUniqueToken(),
            'is_active'  => true,
            'expires_at' => $expiresInDays ? now()->addDays($expiresInDays) : null,
        ]);
    }

    public function regenerate(EventRequest $eventRequest, ?int $expiresInDays = null): EventRequestPublicToken
    {
        $eventRequest->tokens()->active()->update([
            'is_active'  => false,
            'revoked_at' => now(),
        ]);

        return $this->issue($eventRequest, $expiresInDays);
    }

    public function deactivate(EventRequest $eventRequest): void
    {
        $eventRequest->tokens()->active()->update([
            'is_active'  => false,
            'revoked_at' => now(),
        ]);
    }

    /**
     * Resolve a raw token string to its Event Request, or null if the link
     * is invalid, revoked, or expired. Never leaks which reason it was.
     */
    public function resolve(string $token): ?EventRequest
    {
        $record = EventRequestPublicToken::where('token', $token)->first();

        if (! $record || ! $record->isUsable()) {
            return null;
        }

        return $record->eventRequest;
    }

    private function generateUniqueToken(): string
    {
        do {
            $token = Str::random(24);
        } while (EventRequestPublicToken::where('token', $token)->exists());

        return $token;
    }
}
