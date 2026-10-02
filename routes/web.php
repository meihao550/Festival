<?php

use App\Http\Controllers\MolkkyGameController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

Route::get('/', [RankingController::class, 'overall'])->name('rankings.overall');

Route::get('/competitions/{competition}', [RankingController::class, 'competition'])
    ->name('rankings.competition');
Route::put('/competitions/{competition}/ranks', [RankingController::class, 'updateRanks'])
    ->name('rankings.updateRanks');

Route::get('/teams',           [TeamController::class, 'index'])->name('teams.index');
Route::post('/teams',          [TeamController::class, 'store'])->name('teams.store');
Route::delete('/teams/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');
Route::post('/teams/{team}/members',           [TeamController::class, 'addMember'])->name('teams.members.store');
Route::delete('/teams/{team}/members/{member}', [TeamController::class, 'removeMember'])->name('teams.members.destroy');

Route::prefix('molkky')->name('molkky.')->group(function () {
    Route::get('/',                     [MolkkyGameController::class, 'index'])->name('index');
    Route::post('/',                    [MolkkyGameController::class, 'store'])->name('store');
    Route::get('/{game}',               [MolkkyGameController::class, 'show'])->name('show');
    Route::post('/{game}/turns',        [MolkkyGameController::class, 'recordTurn'])->name('turns.store');
    Route::delete('/{game}/turns/last', [MolkkyGameController::class, 'undoTurn'])->name('turns.undo');
    Route::delete('/{game}',            [MolkkyGameController::class, 'destroy'])->name('destroy');
});

Route::view('/rules/molkky', 'rules.molkky')->name('rules.molkky');
Route::view('/timer',        'tools.timer')->name('tools.timer');

// UI には出していないが、必要時に手動で叩けるよう維持
Route::post('/reset', [RankingController::class, 'reset'])->name('rankings.reset');
