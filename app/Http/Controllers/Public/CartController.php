<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\Cart\Cart;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function __invoke(Cart $cart): Response
    {
        $summary = $cart->summary();

        return Inertia::render('Cart/Index', [
            'seo' => [
                'title' => 'Carrito',
                'description' => 'Revisa los libros agregados a tu carrito.',
            ],
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
            'cover' => $product->cover?->path,
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
