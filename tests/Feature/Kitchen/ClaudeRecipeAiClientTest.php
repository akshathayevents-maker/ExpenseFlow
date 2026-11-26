<?php

use Anthropic\Client;
use App\Services\Ai\ClaudeRecipeAiClient;
use App\Services\Ai\RecipeAiException;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/** PSR-18 transport that records requests and returns canned responses — no network. */
class RecordingTransport implements ClientInterface
{
    public array $requests = [];

    public function __construct(private array $responses) {}

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requests[] = ['headers' => $request->getHeaders(), 'body' => json_decode((string) $request->getBody(), true)];

        return array_shift($this->responses);
    }
}

function claudeReply(array $json, string $stop = 'end_turn'): Response
{
    return new Response(200, ['Content-Type' => 'application/json', 'request-id' => 'req_1'], json_encode([
        'id' => 'msg_1', 'type' => 'message', 'role' => 'assistant', 'model' => 'claude-opus-5-5', 'stop_reason' => $stop,
        'stop_sequence' => null, 'content' => [['type' => 'text', 'text' => json_encode($json)]],
        'usage' => ['input_tokens' => 10, 'output_tokens' => 20],
    ]));
}

function claudeClientWith(RecordingTransport $t): ClaudeRecipeAiClient
{
    config(['services.anthropic.api_key' => 'test-key', 'services.anthropic.model' => 'claude-opus-5-5', 'services.anthropic.effort' => 'high']);

    return new ClaudeRecipeAiClient(new Client(apiKey: 'test-key', requestOptions: ['transporter' => $t, 'maxRetries' => 0]));
}

it('sends a structured-output request with server-side fallbacks and parses the recipe', function () {
    $t = new RecordingTransport([claudeReply(fakeRecipe('Lemon Rice'))]);
    $out = claudeClientWith($t)->generateRecipe('Lemon Rice', ['category' => 'Main Course', 'food_type' => 'veg', 'yield_people' => 100]);

    expect($out['recipe']['name'])->toBe('Lemon Rice')->and($out['model'])->toBe('claude-opus-5-5');
    $body = $t->requests[0]['body'];
    expect($body['model'])->toBe('claude-opus-5-5')
        ->and($body['output_config']['format']['type'])->toBe('json_schema')
        ->and($body['output_config']['effort'])->toBe('high')
        ->and($body['output_config']['format']['schema']['properties']['category']['enum'])->toContain('Breakfast')
        ->and($body['fallbacks'])->toBe('default')
        ->and($body)->not->toHaveKey('thinking')
        ->and($body['messages'][0]['content'])->toContain('100 people');
    expect(implode(',', $t->requests[0]['headers']['anthropic-beta'] ?? []))->toContain('server-side-fallback-2026-07-01');
});

it('turns refusals, cut-off answers and bad keys into clear errors', function () {
    $refusal = new Response(200, ['Content-Type' => 'application/json'], json_encode([
        'id' => 'msg_2', 'type' => 'message', 'role' => 'assistant', 'model' => 'claude-opus-5-5', 'stop_reason' => 'refusal',
        'stop_sequence' => null, 'stop_details' => ['type' => 'refusal', 'category' => null, 'explanation' => 'policy'],
        'content' => [], 'usage' => ['input_tokens' => 1, 'output_tokens' => 0],
    ]));
    expect(fn () => claudeClientWith(new RecordingTransport([$refusal]))->planDishes([], 3, []))->toThrow(RecipeAiException::class, 'declined');

    $cut = claudeReply(['dishes' => ['A']], 'max_tokens');
    try {
        claudeClientWith(new RecordingTransport([$cut]))->planDishes([], 3, []);
    } catch (RecipeAiException $e) {
        expect($e->retryable)->toBeTrue();
    }

    $unauth = new Response(401, ['Content-Type' => 'application/json'], json_encode(['type' => 'error', 'error' => ['type' => 'authentication_error', 'message' => 'invalid x-api-key']]));
    try {
        claudeClientWith(new RecordingTransport([$unauth]))->planDishes([], 3, []);
        $this->fail('expected exception');
    } catch (RecipeAiException $e) {
        expect($e->retryable)->toBeFalse()->and($e->getMessage())->toContain('ANTHROPIC_API_KEY');
    }
});

it('asks for dish names excluding the existing library', function () {
    $t = new RecordingTransport([claudeReply(['dishes' => ['Lemon Rice', ' Curd Rice ', 'Lemon Rice']])]);
    $names = claudeClientWith($t)->planDishes(['category' => 'Main Course', 'cuisine' => 'Tamil Nadu'], 2, ['Sambar']);
    expect($names)->toBe(['Lemon Rice', 'Curd Rice'])->and($t->requests[0]['body']['messages'][0]['content'])->toContain('Sambar');
});
