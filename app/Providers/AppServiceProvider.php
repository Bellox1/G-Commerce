<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale(config('app.locale', 'fr'));

        Carbon::macro('fr', function (string $format = 'd F Y'): string {
            return ucwords($this->translatedFormat($format));
        });

        Blade::directive('prix', function ($expression) {
            return "<?php echo (function (\$v) {
                \$v = (float) \$v;
                if (abs(\$v) < 0.001) return '0 F';
                \$neg = \$v < 0;
                \$val = number_format(round(abs(\$v)), 0, ',', ' ');
                return (\$neg ? '-' : '') . \$val . ' F';
            })($expression); ?>";
        });
    }
}
