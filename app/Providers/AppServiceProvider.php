<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

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
        $this->configureDefaults();
        $this->configureRateLimiting();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        if (app()->isProduction()) {
            URL::forceScheme('https');
        }

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('mpesa-stk', function (Request $request) {
            $phone = Str::transliterate(Str::lower((string) $request->input('mpesa_phone')));

            return [
                Limit::perMinute(8)->by($request->ip()),
                Limit::perMinute(4)->by($phone !== '' ? $phone : $request->ip()),
            ];
        });

        RateLimiter::for('mpesa-status', function (Request $request) {
            return Limit::perMinute(40)->by($request->ip());
        });

        RateLimiter::for('mpesa-callback', function (Request $request) {
            return Limit::perMinute(120)->by($request->ip());
        });
    }
}
