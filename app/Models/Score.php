<?php

namespace App\Models;

use App\Enums\Competition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Score extends Model
{
    protected $fillable = ['team_member_id', 'competition_id', 'rank'];

    protected function casts(): array
    {
        return [
            'rank'           => 'integer',
            'competition_id' => Competition::class,
        ];
    }

    public function teamMember(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class);
    }
}
