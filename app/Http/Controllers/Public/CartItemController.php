<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\Product;
use App\Support\Cart\Cart;
use Illuminate\Http\RedirectResponse;

class CartItemController extends Controller
{
    public function store(StoreCartItemRequest $request, Product $product, Cart $cart): RedirectResponse
    {
        $cart->add($product, $request->integer('quantity'));

        return back()->with('success', 'Libro agregado al carrito.');
    }

    public function update(UpdateCartItemRequest $request, Product $product, Cart $cart): RedirectResponse
    {
        $cart->update($product, $request->integer('quantity'));

        return back()->with('success', 'Cantidad actualizada.');
    }

    public function destroy(Product $product, Cart $cart): RedirectResponse
    {
        $cart->remove($product);

        return back()->with('success', 'Libro eliminado del carrito.');
    }
}
