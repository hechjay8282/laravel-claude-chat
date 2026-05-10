<?php

use Hakim\ClaudeChat\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::middleware(config('claude-chat.middleware', ['web', 'auth']))
    ->prefix(config('claude-chat.route_prefix', 'chat'))
    ->group(function () {
        Route::post('/',       [ChatController::class, 'send'])->name('chat.send');
        Route::delete('/',     [ChatController::class, 'clear'])->name('chat.clear');
    });
