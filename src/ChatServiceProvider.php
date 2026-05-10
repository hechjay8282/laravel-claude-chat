<?php

namespace Hakim\ClaudeChat;

use Hakim\ClaudeChat\Http\Controllers\ChatController;
use Illuminate\Support\ServiceProvider;

class ChatServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/claude-chat.php', 'claude-chat');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'claude-chat');

        $this->publishes([
            __DIR__ . '/../config/claude-chat.php' => config_path('claude-chat.php'),
        ], 'claude-chat-config');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/claude-chat'),
        ], 'claude-chat-views');
    }
}
