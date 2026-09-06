<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\Cart\Cart;
use App\Support\Seo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function __invoke(Request $request, Cart $cart): Response
    {
        $summary = $cart->summary();

        return Inertia::render('Cart/Index', [
            'seo' => Seo::page(
                $request,
                'Carrito',
                'Revisa los libros agregados a tu carrito.',
                [['name' => 'Inicio', 'href' => '/'], ['name' => 'Carrito', 'href' => '/carrito']],
                noindex: true,
            ),
            'cart' => [
                'items' => $summary['items']->map(fn (array $item): array => $this->itemPayload($item))->all(),
                'subtotal' => $summary['subtotal'],
            ],
        ]);
    }

    /**
     * @param  array{product: Product, quantity: int, subtotal: int}  $item
     * @return array<string, mixed>
     */
    private function itemPayload(array $item): array
    {
        $product = $item['product'];

        return [
            'id' => $product->getKey(),
            'title' => $product->title,
            'author' => $product->authors->pluck('name')->join(', ') ?: null,
            'cover' => $product->cover?->url,
            'price' => $product->price_minor,
            'quantity' => $item['quantity'],
            'subtotal' => $item['subtotal'],
            'availability' => $product->availability,
            'href' => route('books.show', $product, absolute: false),
            'updateHref' => route('cart.items.update', $product, absolute: false),
            'destroyHref' => route('cart.items.destroy', $product, absolute: false),
        ];
    }
}
