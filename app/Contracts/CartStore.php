<?php

namespace App\Contracts;

interface CartStore
{
    /**
     * @return array<int, int>
     */
    public function quantities(): array;

    public function put(int $productId, int $quantity): void;

    public function forget(int $productId): void;
}
