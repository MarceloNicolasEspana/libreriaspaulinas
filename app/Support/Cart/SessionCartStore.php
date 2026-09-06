<?php

namespace App\Support\Cart;

use App\Contracts\CartStore;
use Illuminate\Session\Store;

class SessionCartStore implements CartStore
{
    private const SESSION_KEY = 'cart.items';

    public function __construct(private Store $session) {}

    /**
     * @return array<int, int>
     */
    public function quantities(): array
    {
        $items = $this->session->get(self::SESSION_KEY, []);

        if (! is_array($items)) {
            return [];
        }

        $quantities = [];

        foreach ($items as $productId => $quantity) {
            if (is_numeric($productId) && is_int($quantity) && $quantity > 0) {
                $quantities[(int) $productId] = $quantity;
            }
        }

        return $quantities;
    }

    public function put(int $productId, int $quantity): void
    {
        $items = $this->quantities();
        $items[$productId] = $quantity;

        $this->session->put(self::SESSION_KEY, $items);
    }

    public function forget(int $productId): void
    {
        $items = $this->quantities();
        unset($items[$productId]);

        $this->session->put(self::SESSION_KEY, $items);
    }
}
