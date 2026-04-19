<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        \Laravel\Passport\Passport::tokensExpireIn(now()->addHours(6));
        \Laravel\Passport\Passport::refreshTokensExpireIn(now()->addDays(30));
        \Laravel\Passport\Passport::personalAccessTokensExpireIn(now()->addHours(6));

        \Dedoc\Scramble\Scramble::afterOpenApiGenerated(function (\Dedoc\Scramble\Support\Generator\OpenApi $openApi) {
            $openApi->secure(
                \Dedoc\Scramble\Support\Generator\SecurityScheme::http('bearer')
            );
        });
    }
}
