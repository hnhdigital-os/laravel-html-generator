<?php

namespace HnhDigital\LaravelHtmlGenerator;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class BladeDirectiveServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::directive('icon', function ($icon) {
            $icon = trim($icon, "'\"");

            if (substr($icon, 0, 1) !== '$') {
                $icon = "'$icon'";
            }

            return "<?= (string)Html::icon($icon); ?>";
        });

        Blade::directive('html', function ($html) {
            return "<?= (string)Html::$html; ?>";
        });
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }
}
