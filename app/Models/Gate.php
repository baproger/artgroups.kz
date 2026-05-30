<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Gate extends Model
{
    protected $fillable = ['category_id', 'type', 'name', 'image', 'description', 'colors', 'is_active', 'sort_order'];

    const TYPE_GATE = 'gate';
    const TYPE_WICKET = 'wicket';

    protected $casts = [
        'colors' => 'array',
        'is_active' => 'boolean',
    ];

    protected $appends = ['image_url'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(GateCategory::class, 'category_id');
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        return asset('storage/' . $this->image);
    }
}
