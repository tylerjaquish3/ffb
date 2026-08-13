<?php

use App\Http\Controllers\Admin\DraftImportController;
use App\Http\Controllers\Admin\LeagueSettingController;
use App\Http\Controllers\Admin\RosterPositionController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\StatCategoryController;
use App\Http\Controllers\Admin\StatsController;
use App\Http\Controllers\AddPlayerController;
use App\Http\Controllers\LeagueController;
use App\Http\Controllers\MatchupCommentController;
use App\Http\Controllers\MatchupController;
use App\Http\Controllers\PlayerProfileController;
use App\Http\Controllers\PlayersController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TradeController;
use App\Http\Controllers\WaiverController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', [LeagueController::class, 'index'])->name('dashboard');

    Route::get('/players', [PlayersController::class, 'index'])->name('players.index');
    Route::get('/players/{player}/profile', [PlayerProfileController::class, 'show'])->name('players.profile');
    Route::get('/players/{player}/add', [AddPlayerController::class, 'create'])->name('players.add.create');
    Route::post('/players/{player}/add', [AddPlayerController::class, 'store'])->name('players.add.store');
    Route::get('/players/{player}/bid', [WaiverController::class, 'create'])->name('waivers.create');
    Route::post('/players/{player}/bid', [WaiverController::class, 'store'])->name('waivers.store');

    Route::middleware('commissioner')->group(function () {
        Route::get('/players/create', [PlayersController::class, 'create'])->name('players.create');
        Route::post('/players', [PlayersController::class, 'store'])->name('players.store');
        Route::get('/players/{player}/edit', [PlayersController::class, 'edit'])->name('players.edit');
        Route::put('/players/{player}', [PlayersController::class, 'update'])->name('players.update');
        Route::delete('/players/{player}', [PlayersController::class, 'destroy'])->name('players.destroy');
    });

    Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');
    Route::put('/teams/{team}', [TeamController::class, 'update'])->name('teams.update');
    Route::put('/teams/{team}/lineup', [TeamController::class, 'updateLineup'])->name('teams.lineup.update');
    Route::delete('/teams/{team}/roster/{player}', [TeamController::class, 'dropPlayer'])->name('teams.roster.drop');
    Route::get('/teams/{team}/trade/create', [TradeController::class, 'create'])->name('trades.create');

    Route::post('/trades', [TradeController::class, 'store'])->name('trades.store');
    Route::get('/trades/{trade}', [TradeController::class, 'show'])->name('trades.show');
    Route::post('/trades/{trade}/accept', [TradeController::class, 'accept'])->name('trades.accept');
    Route::post('/trades/{trade}/decline', [TradeController::class, 'decline'])->name('trades.decline');
    Route::post('/trades/{trade}/cancel', [TradeController::class, 'cancel'])->name('trades.cancel');
    Route::post('/trades/{trade}/veto', [TradeController::class, 'veto'])->name('trades.veto');
    Route::get('/trades/{trade}/counter', [TradeController::class, 'counter'])->name('trades.counter');
    Route::get('/trades/{trade}/resolve', [TradeController::class, 'resolve'])->name('trades.resolve');
    Route::post('/trades/{trade}/resolve', [TradeController::class, 'resolveStore'])->name('trades.resolve.store');

    Route::get('/matchup', [MatchupController::class, 'mine'])->name('matchups.mine');
    Route::get('/matchups/{matchup}', [MatchupController::class, 'show'])->name('matchups.show');
    Route::post('/matchups/{matchup}/comments', [MatchupCommentController::class, 'store'])->name('matchups.comments.store');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'commissioner'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('roster-positions', RosterPositionController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    Route::resource('stat-categories', StatCategoryController::class)
        ->only(['index', 'store', 'destroy']);
    Route::put('stat-categories', [StatCategoryController::class, 'updateAll'])->name('stat-categories.update-all');

    Route::get('draft-import', [DraftImportController::class, 'index'])->name('draft-import.index');
    Route::post('draft-import', [DraftImportController::class, 'store'])->name('draft-import.store');
    Route::post('draft-import/sync-players', [DraftImportController::class, 'syncPlayers'])->name('draft-import.sync-players');
    Route::post('draft-import/sync-schedule', [DraftImportController::class, 'syncSchedule'])->name('draft-import.sync-schedule');
    Route::post('draft-import/sync-injuries', [DraftImportController::class, 'syncInjuries'])->name('draft-import.sync-injuries');

    Route::get('schedule', [ScheduleController::class, 'index'])->name('schedule.index');
    Route::post('schedule/generate', [ScheduleController::class, 'generate'])->name('schedule.generate');
    Route::post('schedule', [ScheduleController::class, 'store'])->name('schedule.store');
    Route::delete('schedule/{matchup}', [ScheduleController::class, 'destroy'])->name('schedule.destroy');

    Route::get('stats', [StatsController::class, 'index'])->name('stats.index');
    Route::post('stats', [StatsController::class, 'store'])->name('stats.store');

    Route::get('league-settings', [LeagueSettingController::class, 'edit'])->name('league-settings.edit');
    Route::put('league-settings', [LeagueSettingController::class, 'update'])->name('league-settings.update');
});

require __DIR__.'/auth.php';
