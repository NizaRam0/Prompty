<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

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
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        RateLimiter::for('api', function (Request $request) {

            return Limit::perMinute(60)
            ->by($request->user()?->id ?: $request->ip());
        });

        // Custom rate limiter for prompt generation
        RateLimiter::for('prompt-generation', function (Request $request) {
            $user = $request->user();
            $allowedEmails = ['nizar@gmail.com', 'elnizarramadan61@gmail.com']; // <-- Replace with your email(s)
            if ($user && in_array($user->email, $allowedEmails)) {
                return Limit::none(); // No limit for allowed emails
            }
            return Limit::perDay(5)->by($user ? $user->id : $request->ip());
        });

        // Scramble::afterOpenApiGenerated(fn (OpenApi $openApi) => $openApi->secure(
        //     SecurityScheme::http('bearer', 'JWT')->as('bearerAuth')
        // ));
    }
}
