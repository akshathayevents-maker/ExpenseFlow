<?php

namespace App\Services\Ai;

use Anthropic\Client;
use Anthropic\Core\Exceptions\APIConnectionException;
use Anthropic\Core\Exceptions\APIStatusException;
use Anthropic\Core\Exceptions\AuthenticationException;
use Anthropic\Core\Exceptions\BadRequestException;
use Anthropic\Core\Exceptions\InternalServerException;
use Anthropic\Core\Exceptions\PermissionDeniedException;
use Anthropic\Core\Exceptions\RateLimitException;
use App\Models\Recipe;

/**
 * Claude (Anthropic) implementation. Server-side only; the key comes from config/services.php (ANTHROPIC_API_KEY).
 * One request = one recipe (or one dish list), returned as JSON constrained by a schema (structured outputs).
 */
class ClaudeRecipeAiClient implements RecipeAiClient
{
    private const SYSTEM = <<<'TXT'
You write production recipes for a commercial catering kitchen in Tamil Nadu, India that cooks for 25–500+ guests
(weddings, corporate meals, daily staff meals). Your recipes are used for production planning, store issue of raw
materials and costing, so quantities must be realistic for the stated batch size and in metric units.

Rules:
- Quantities are for the WHOLE batch (not per person) and must be practical for commercial cooking at that scale.
- Use metric units only: kg, gram, litre, ml, piece, bundle, packet, dozen. Prefer kg / litre for anything over 1 kg / 1 litre.
- Give a numeric quantity for every ingredient, including salt, water and oil. Only when a quantity truly cannot be
  standardised (e.g. "to garnish") set quantity to null and give display_quantity; if a number is an estimate set approximate=true.
- Use plain canonical ingredient names a store would stock (e.g. "Toor Dal", "Raw Rice", "Sunflower Oil", "Curry Leaves").
  Put cutting or soaking details in prep_note, not in the name.
- Steps are numbered in order, written for kitchen staff: say the heat level, vessel, timings and what to look for
  (texture, colour, aroma, consistency). Never write "cook until done". Mention food-safety points where they matter
  (washing, soaking times, holding temperature, cooling, cross-contamination for meat and fish).
- Write everything in English AND Tamil. Tamil must be natural, simple spoken-kitchen Tamil that a cook can follow
  (e.g. "வெங்காயத்தை பொன்னிறமாக வதக்கவும்"), using common words like வெங்காயம், தக்காளி, இஞ்சி பூண்டு விழுது, மஞ்சள் தூள்,
  நெய், தயிர், புதினா, கொத்தமல்லி; familiar English words in Tamil script are fine when kitchens use them (குக்கர், டம்).
  Every step has instruction (English) and instruction_ta (Tamil) saying the same thing; do not leave Tamil empty.
- kitchen_notes: scaling advice (batch splitting, vessel size, water/spice adjustment) and food-safety points, in English and Tamil.
- Respect the requested food type: vegetarian recipes contain no meat, fish or egg; vegan recipes also no dairy or ghee.
TXT;

    public function __construct(private ?Client $client = null) {}

    public function isConfigured(): bool
    {
        return (bool) config('services.anthropic.api_key');
    }

    public function providerName(): string
    {
        return 'anthropic';
    }

    public function modelName(): string
    {
        return (string) config('services.anthropic.model', 'claude-opus-5-5');
    }

    public function planDishes(array $config, int $count, array $exclude): array
    {
        $prompt = "Propose {$count} different, commonly cooked dishes for a catering menu.\n"
            .$this->describe($config)
            ."\nDo NOT propose any of these existing recipes or close variants of them: "
            .($exclude ? implode('; ', array_slice($exclude, 0, 400)) : '(none)')
            ."\nUse the usual name a South Indian kitchen would use. Each dish must be distinct from the others.";

        $schema = [
            'type' => 'object',
            'properties' => ['dishes' => ['type' => 'array', 'items' => ['type' => 'string']]],
            'required' => ['dishes'],
            'additionalProperties' => false,
        ];

        [$data] = $this->ask($prompt, $schema, 4000);

        return array_values(array_unique(array_filter(array_map(fn ($d) => trim((string) $d), $data['dishes'] ?? []))));
    }

    public function generateRecipe(string $dish, array $config): array
    {
        $people = (int) ($config['yield_people'] ?? 100);
        $prompt = "Write the production recipe for: {$dish}\n"
            .$this->describe($config)
            ."\nBatch size: {$people} people (set yield_per_batch to {$people} and yield_unit to \"people\").";

        [$data, $requestId, $model] = $this->ask($prompt, $this->recipeSchema(), 16000);

        return ['recipe' => $data, 'request_id' => $requestId, 'model' => $model];
    }

    private function describe(array $c): string
    {
        $lines = [];
        foreach (['category' => 'Category', 'group' => 'Dish group', 'cuisine' => 'Cuisine'] as $k => $label) {
            if (! empty($c[$k])) {
                $lines[] = "{$label}: {$c[$k]}";
            }
        }
        if (! empty($c['food_type'])) {
            $lines[] = 'Food type: '.(Recipe::FOOD_TYPES[$c['food_type']] ?? $c['food_type']);
        }
        if (! empty($c['notes'])) {
            $lines[] = 'Notes from the kitchen manager: '.mb_substr((string) $c['notes'], 0, 500);
        }

        return implode("\n", $lines);
    }

    private function recipeSchema(): array
    {
        $str = ['type' => 'string'];
        $nullableNum = ['type' => ['number', 'null']];
        $nullableInt = ['type' => ['integer', 'null']];
        $nullableStr = ['type' => ['string', 'null']];

        return [
            'type' => 'object',
            'properties' => [
                'name' => $str,
                'name_ta' => $str,
                'description' => $str,
                'kitchen_notes' => $str,
                'kitchen_notes_ta' => $str,
                'category' => ['type' => 'string', 'enum' => Recipe::categories()],
                'cuisine' => $str,
                'food_type' => ['type' => 'string', 'enum' => array_keys(Recipe::FOOD_TYPES)],
                'yield_per_batch' => ['type' => 'number'],
                'yield_unit' => $str,
                'prep_time_minutes' => ['type' => 'integer'],
                'cook_time_minutes' => ['type' => 'integer'],
                'ingredients' => ['type' => 'array', 'items' => [
                    'type' => 'object',
                    'properties' => [
                        'name' => $str, 'name_ta' => $str, 'quantity' => $nullableNum, 'unit' => $nullableStr, 'prep_note' => $nullableStr,
                        'display_quantity' => $nullableStr, 'approximate' => ['type' => 'boolean'], 'optional' => ['type' => 'boolean'],
                    ],
                    'required' => ['name', 'name_ta', 'quantity', 'unit', 'prep_note', 'display_quantity', 'approximate', 'optional'],
                    'additionalProperties' => false,
                ]],
                'steps' => ['type' => 'array', 'items' => [
                    'type' => 'object',
                    'properties' => ['title' => $str, 'title_ta' => $str, 'instruction' => $str, 'instruction_ta' => $str,
                        'note' => $nullableStr, 'note_ta' => $nullableStr, 'duration_minutes' => $nullableInt],
                    'required' => ['title', 'title_ta', 'instruction', 'instruction_ta', 'note', 'note_ta', 'duration_minutes'],
                    'additionalProperties' => false,
                ]],
            ],
            'required' => ['name', 'name_ta', 'description', 'kitchen_notes', 'kitchen_notes_ta', 'category', 'cuisine', 'food_type', 'yield_per_batch', 'yield_unit',
                'prep_time_minutes', 'cook_time_minutes', 'ingredients', 'steps'],
            'additionalProperties' => false,
        ];
    }

    /** @return array{0: array, 1: ?string, 2: string} decoded JSON, request id, model that answered */
    private function ask(string $prompt, array $schema, int $maxTokens): array
    {
        if (! $this->isConfigured()) {
            throw new RecipeAiException('AI generation is not configured (ANTHROPIC_API_KEY is empty).');
        }
        $client = $this->client ??= new Client(
            apiKey: (string) config('services.anthropic.api_key'),
            requestOptions: ['timeout' => (float) config('services.anthropic.timeout', 300), 'maxRetries' => 2],
        );

        try {
            $message = $client->beta->messages->create(
                model: $this->modelName(),
                maxTokens: $maxTokens,
                system: self::SYSTEM,
                messages: [['role' => 'user', 'content' => $prompt]],
                outputConfig: [
                    'effort' => (string) config('services.anthropic.effort', 'high'),
                    'format' => ['type' => 'json_schema', 'schema' => $schema],
                ],
                // on a policy decline the API re-runs the request on the server-defined fallback model
                fallbacks: 'default',
                betas: ['server-side-fallback-2026-07-01'],
            );
        } catch (RateLimitException|InternalServerException|APIConnectionException $e) {
            throw new RecipeAiException('AI service busy or unreachable: '.$e->getMessage(), true, $e);
        } catch (AuthenticationException|PermissionDeniedException $e) {
            throw new RecipeAiException('AI key rejected — check ANTHROPIC_API_KEY.', false, $e);
        } catch (BadRequestException $e) {
            throw new RecipeAiException('AI request rejected: '.$e->getMessage(), false, $e);
        } catch (APIStatusException $e) {
            throw new RecipeAiException('AI error: '.$e->getMessage(), ($e->status ?? 0) >= 500, $e);
        }

        if ($message->stopReason === 'refusal') {
            throw new RecipeAiException('The AI declined this request'.($message->stopDetails?->explanation ? ': '.$message->stopDetails->explanation : '.'));
        }
        if ($message->stopReason === 'max_tokens') {
            throw new RecipeAiException('The AI answer was cut off (too long).', true);
        }
        $text = '';
        foreach ($message->content as $block) {
            if ($block->type === 'text') {
                $text .= $block->text;
            }
        }
        $data = json_decode($text, true);
        if (! is_array($data)) {
            throw new RecipeAiException('The AI answer was not valid JSON.', true);
        }

        return [$data, $message->id ?? null, $message->model ?? $this->modelName()];
    }
}
