<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['type', 'title', 'subtitle', 'body', 'is_visible', 'sort_order'])]
class Content extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('is_visible', true)
            ->whereNotNull('published_at');
    }

    public function media(): HasOne
    {
        return $this->hasOne(ContentMedia::class);
    }
}
