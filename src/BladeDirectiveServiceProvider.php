<?php

namespace Bluora\LaravelHtmlGenerator;

use Blade;
use Illuminate\Support\ServiceProvider;

class BladeDirectiveServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        blade::directive('icon', function ($icon) {
            return "<?= (string)Html::icon('$icon'); ?>";
        });

        blade::directive('html', function ($html) {
            return "<?= (string)Html::$html; ?>";
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
