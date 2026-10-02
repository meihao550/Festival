<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    protected $fillable = ['team_id', 'started_at', 'ended_at', 'winner_player_id'];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at'   => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class)->orderBy('position');
    }

    public function winnerPlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'winner_player_id');
    }

    public function isFinished(): bool
    {
        return $this->ended_at !== null;
    }
}
