<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuDraft extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'venue', 'event_date', 'people_count', 'content', 'created_by',
    ];

    protected $casts = [
        'event_date'   => 'date',
        'people_count' => 'integer',
        'content'      => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Return content in the new ordered-array format.
     * Automatically migrates old keyed-object format on read — no data loss.
     *
     * New format: [{key, label_en, label_ta, items}, ...]
     * Old format: {breakfast:[], lunch:[], dinner:[], evening_snacks:[]}
     */
    public function normalizedContent(): array
    {
        $content = $this->content ?? [];
        return self::normalizeContentArray($content);
    }

    public static function normalizeContentArray(array $content): array
    {
        // New format: sequential array with 'key' entries
        if (array_is_list($content) && (empty($content) || isset($content[0]['key']))) {
            return $content;
        }

        // Old format: associative object — migrate to array
        $legacy   = config('menu_categories.sections', []);
        $sections = [];
        foreach (['breakfast', 'lunch', 'dinner', 'evening_snacks'] as $k) {
            if (! empty($content[$k])) {
                $sections[] = [
                    'key'      => $k,
                    'label_en' => $legacy[$k]['en'] ?? ucwords(str_replace('_', ' ', $k)),
                    'label_ta' => $legacy[$k]['ta'] ?? '',
                    'items'    => array_values($content[$k]),
                ];
            }
        }
        return $sections;
    }

    public function totalItems(): int
    {
        return array_sum(array_map(fn($s) => count($s['items'] ?? []), $this->normalizedContent()));
    }

    public function formattedDate(): ?string
    {
        return $this->event_date?->format('d M Y');
    }
}
