<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('games', 'winner_player_id')) {
            return; // 既に存在する場合はスキップ (idempotent)
        }

        Schema::table('games', function (Blueprint $table) {
            $table->foreignId('winner_player_id')
                ->nullable()
                ->after('ended_at')
                ->constrained('players')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropForeign(['winner_player_id']);
            $table->dropColumn('winner_player_id');
        });
    }
};
