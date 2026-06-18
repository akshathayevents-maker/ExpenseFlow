<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuTemplate extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'content', 'created_by'];

    protected $casts = ['content' => 'array'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function normalizedContent(): array
    {
        return MenuDraft::normalizeContentArray($this->content ?? []);
    }

    public function totalItems(): int
    {
        return array_sum(array_map(fn($s) => count($s['items'] ?? []), $this->normalizedContent()));
    }
}
