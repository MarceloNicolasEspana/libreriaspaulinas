<?php

namespace App\Support\Cart;

use App\Contracts\CartStore;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class Cart
{
    public function __construct(private CartStore $store) {}

    public function add(Product $product, int $quantity): void
    {
        $quantity += $this->quantityFor($product);

        $this->assertPurchasable($product, $quantity);
        $this->store->put($product->getKey(), $quantity);
    }

    public function update(Product $product, int $quantity): void
    {
        $this->assertPurchasable($product, $quantity);
        $this->store->put($product->getKey(), $quantity);
    }

    public function remove(Product $product): void
    {
        $this->store->forget($product->getKey());
    }

    public function quantityFor(Product $product): int
    {
        return $this->store->quantities()[$product->getKey()] ?? 0;
    }

    public function count(): int
    {
        return array_sum($this->store->quantities());
    }

    /**
     * @return array{items: Collection<int, array{product: Product, quantity: int, subtotal: int}>, subtotal: int}
     */
    public function summary(): array
    {
        $quantities = $this->store->quantities();

        if ($quantities === []) {
            return ['items' => collect(), 'subtotal' => 0];
        }

        $products = Product::query()
            ->active()
            ->whereKey(array_keys($quantities))
            ->with([
                'authors:authors.id,authors.name',
                'cover:id,product_id,path,alt,sort_order',
            ])
            ->get()
            ->keyBy(fn (Product $product): int => $product->getKey());

        $items = collect($quantities)
            ->map(function (int $quantity, int $productId) use ($products): ?array {
                $product = $products->get($productId);

                if (! $product instanceof Product) {
                    $this->store->forget($productId);

                    return null;
                }

                return [
                    'product' => $product,
                    'quantity' => $quantity,
                    'subtotal' => $product->price_minor * $quantity,
                ];
            })
            ->filter()
            ->values();

        return [
            'items' => $items,
            'subtotal' => $items->sum('subtotal'),
        ];
    }

    private function assertPurchasable(Product $product, int $quantity): void
    {
        if (! $product->active) {
            throw ValidationException::withMessages([
                'product' => 'Este producto no está disponible para compra.',
            ]);
        }

        if ($quantity < 1) {
            throw ValidationException::withMessages([
                'quantity' => 'La cantidad debe ser al menos 1.',
            ]);
        }

        if ($quantity > $product->stock) {
            throw ValidationException::withMessages([
                'quantity' => 'La cantidad solicitada supera el stock disponible.',
            ]);
        }
    }
}
