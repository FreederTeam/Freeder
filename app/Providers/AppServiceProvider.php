<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    public function boot()
    {
        if (app('db')->connection() instanceof \Illuminate\Database\SQLiteConnection) {
            app('db')->statement(app('db')->raw('PRAGMA foreign_keys=1'));
        }
    }
}
