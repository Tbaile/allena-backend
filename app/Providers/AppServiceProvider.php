<?php

namespace App\Providers;

use App\Models\User;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::before(function (User $user, string $ability): ?bool {
            return $user->hasRole('admin') ? true : null;
        });

        Scramble::configure()
            ->withDocumentTransformers(function (OpenApi $openApi): void {
                /** @var SecurityScheme $scheme */
                $scheme = SecurityScheme::http('bearer');
                $openApi->secure($scheme);
            });
    }
}
