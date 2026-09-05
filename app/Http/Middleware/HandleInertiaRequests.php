<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * Plantilla Blade que envuelve la aplicación Vue.
     */
    protected $rootView = 'app';

    /**
     * Datos compartidos con todas las páginas Inertia.
     *
     * Aquí solo van datos globales y baratos de calcular. Los datos propios de
     * cada página los entrega su controlador.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),

            'institution' => fn (): array => [
                'shortName' => config('paulinas.short_name'),
                'legalName' => config('paulinas.legal_name'),
                'tagline' => config('paulinas.tagline'),
                'address' => config('paulinas.address'),
                'contact' => config('paulinas.contact'),
                'social' => array_filter(config('paulinas.social')),
            ],

            'navigation' => fn (): array => [
                'primary' => config('navigation.primary'),
                'footer' => config('navigation.footer'),
            ],

            'currency' => fn (): array => config('paulinas.currency'),

            'auth' => [
                'user' => $request->user(),
            ],

            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
