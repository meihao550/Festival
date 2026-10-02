<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Turn extends Model
{
    protected $fillable = ['player_id', 'points', 'total_after'];

    protected function casts(): array
    {
        return [
            'points'      => 'integer',
            'total_after' => 'integer',
        ];
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function isMiss(): bool
    {
        return $this->points === 0;
    }
}
