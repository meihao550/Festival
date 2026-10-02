<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $fillable = ['name'];

    public function members(): HasMany
    {
        return $this->hasMany(TeamMember::class);
    }

    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }

    /**
     * 名前の並べ替え用キー: 半角カナ → 全角カナ (濁点合成) → ひらがな に正規化
     */
    public static function kanaKey(string $name): string
    {
        return mb_convert_kana(mb_convert_kana($name, 'KV'), 'c');
    }
}
