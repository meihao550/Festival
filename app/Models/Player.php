<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Player extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_DISQUALIFIED = 'disqualified';

    protected $fillable = ['game_id', 'team_member_id', 'position', 'status'];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function teamMember(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class);
    }

    public function turns(): HasMany
    {
        return $this->hasMany(Turn::class)->orderBy('id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function currentTotal(): int
    {
        return (int) ($this->turns()->reorder('id', 'desc')->value('total_after') ?? 0);
    }

    public function isDisqualified(): bool
    {
        return $this->status === self::STATUS_DISQUALIFIED;
    }

    public function displayName(): string
    {
        return $this->teamMember?->name ?? '(不明)';
    }
}
