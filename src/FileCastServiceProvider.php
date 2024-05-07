<?php

namespace Yaseen\FileCast;

use Illuminate\Support\ServiceProvider;

class FileCastServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__.'/config.php', 'file-cast');
        $this->publishes([
            __DIR__.'/config.php' => config_path('file-cast.php'),
        ], 'file-cast-config');

    }

}
