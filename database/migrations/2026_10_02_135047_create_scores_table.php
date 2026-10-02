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
        // 旧スキーマの残骸を掃除してから作成 (本番の既存テーブルと衝突回避)
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('scores');
        Schema::dropIfExists('participants');
        Schema::dropIfExists('team_participants');
        Schema::dropIfExists('competitions'); // enum化で不要になった
        Schema::enableForeignKeyConstraints();

        Schema::create('scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_member_id')->constrained()->cascadeOnDelete();
            // 競技は App\Enums\Competition の値 (int)。FK なし
            $table->unsignedTinyInteger('competition_id');
            $table->unsignedSmallInteger('rank');
            $table->timestamps();

            $table->unique(['team_member_id', 'competition_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scores');
    }
};
