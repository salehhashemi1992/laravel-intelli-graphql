<?php

namespace Salehhashemi\LaravelIntelliGraphql;

use Illuminate\Support\ServiceProvider;
use Salehhashemi\LaravelIntelliGraphql\Console\AiGraphqlCommand;

class LaravelIntelliGraphqlServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/intelli-graphql.php' => config_path('intelli-graphql.php'),
            ], 'config');

            $this->commands([
                AiGraphqlCommand::class,
            ]);
        }
    }
}
