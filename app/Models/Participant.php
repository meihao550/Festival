<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Participant extends Model
{
    protected $fillable = ['name'];

    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    /**
     * 名前の並べ替え用キー: 半角カナ → 全角カナ (濁点合成) → ひらがな に正規化
     * これにより「あいうえお順」でソートできる
     */
    public static function kanaKey(string $name): string
    {
        return mb_convert_kana(mb_convert_kana($name, 'KV'), 'c');
    }
}