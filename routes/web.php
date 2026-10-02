<?php

use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\RankingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [RankingController::class, 'overall'])->name('rankings.overall');

Route::get('/competitions/{competition}', [RankingController::class, 'competition'])
    ->name('rankings.competition');
Route::put('/competitions/{competition}/ranks', [RankingController::class, 'updateRanks'])
    ->name('rankings.updateRanks');

Route::get('/participants/create', [ParticipantController::class, 'create'])
    ->name('participants.create');
Route::post('/participants', [ParticipantController::class, 'store'])
    ->name('participants.store');

Route::view('/rules/molkky', 'rules.molkky')->name('rules.molkky');

// UI には出していないが、必要時に手動で叩けるよう維持
Route::post('/reset', [RankingController::class, 'reset'])->name('rankings.reset');
