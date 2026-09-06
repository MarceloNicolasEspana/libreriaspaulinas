<?php

namespace App\Http\Middleware;

use App\Support\Admin\Catalog;
use App\Support\Cart\Cart;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function __construct(private Cart $cart) {}

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

            'cartSummary' => fn (): array => [
                'count' => $this->cart->count(),
                'href' => route('cart.index', absolute: false),
            ],

            /*
             * Solo el nombre: la prop viaja en el HTML de todas las páginas,
             * también las públicas, y el correo o los indicadores de permisos
             * de la cuenta no tienen por qué llegar al navegador.
             */
            'auth' => fn (): array => [
                'user' => $request->user() ? ['name' => $request->user()->name] : null,
            ],
            'adminNavigation' => fn (): array => $request->user()?->can('manage-admin')
                ? Catalog::navigation() : [],
            'logoutHref' => fn (): string => route('logout', absolute: false),

            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
