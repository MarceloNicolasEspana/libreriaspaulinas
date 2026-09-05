<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Vite;
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
        Date::use(CarbonImmutable::class);

        // Fuera de producción, fallar de inmediato ante lazy loading, asignación
        // masiva no declarada o acceso a atributos inexistentes.
        Model::shouldBeStrict(! $this->app->isProduction());

        Vite::prefetch(concurrency: 3);
    }
}
