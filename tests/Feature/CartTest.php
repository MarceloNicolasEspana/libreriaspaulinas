<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_active_product_can_be_added_and_the_header_count_is_shared(): void
    {
        $product = Product::factory()->create(['price' => 12990, 'stock' => 5]);

        $this->from(route('books.show', $product))
            ->post(route('cart.items.store', $product), ['quantity' => 2])
            ->assertRedirect(route('books.show', $product))
            ->assertSessionHas('cart.items.'.$product->id, 2);

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page) => $page
                    ->component('Cart/Index')
                    ->where('cartSummary.count', 2)
                    ->where('cart.items.0.id', $product->id)
                    ->where('cart.items.0.quantity', 2)
                    ->where('cart.items.0.subtotal', 25980)
                    ->where('cart.subtotal', 25980)
            );
    }

    public function test_adding_the_same_product_increases_its_quantity(): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        $this->post(route('cart.items.store', $product), ['quantity' => 1]);
        $this->post(route('cart.items.store', $product), ['quantity' => 2])
            ->assertSessionHas('cart.items.'.$product->id, 3);
    }

    public function test_a_cart_item_quantity_can_be_updated(): void
    {
        $product = Product::factory()->create(['stock' => 8]);

        $this->withSession(['cart.items' => [$product->id => 2]])
            ->from(route('cart.index'))
            ->patch(route('cart.items.update', $product), ['quantity' => 4])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('cart.items.'.$product->id, 4);
    }

    public function test_a_cart_item_can_be_removed(): void
    {
        $product = Product::factory()->create();

        $this->withSession(['cart.items' => [$product->id => 2]])
            ->from(route('cart.index'))
            ->delete(route('cart.items.destroy', $product))
            ->assertRedirect(route('cart.index'))
            ->assertSessionMissing('cart.items.'.$product->id);
    }

    public function test_a_quantity_greater_than_stock_is_rejected(): void
    {
        $product = Product::factory()->create(['stock' => 3]);

        $this->from(route('books.show', $product))
            ->post(route('cart.items.store', $product), ['quantity' => 4])
            ->assertRedirect(route('books.show', $product))
            ->assertSessionHasErrors([
                'quantity' => 'La cantidad solicitada supera el stock disponible.',
            ])
            ->assertSessionMissing('cart.items.'.$product->id);
    }

    public function test_the_accumulated_quantity_cannot_exceed_stock(): void
    {
        $product = Product::factory()->create(['stock' => 3]);

        $this->withSession(['cart.items' => [$product->id => 2]])
            ->post(route('cart.items.store', $product), ['quantity' => 2])
            ->assertSessionHasErrors('quantity')
            ->assertSessionHas('cart.items.'.$product->id, 2);
    }

    /**
     * @return array<string, array{int}>
     */
    public static function invalidQuantityProvider(): array
    {
        return [
            'zero' => [0],
            'negative' => [-1],
        ];
    }

    #[DataProvider('invalidQuantityProvider')]
    public function test_zero_and_negative_quantities_are_rejected(int $quantity): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        $this->post(route('cart.items.store', $product), ['quantity' => $quantity])
            ->assertSessionHasErrors([
                'quantity' => 'La cantidad debe ser al menos 1.',
            ])
            ->assertSessionMissing('cart.items.'.$product->id);
    }

    public function test_an_inactive_product_cannot_be_added(): void
    {
        $product = Product::factory()->inactive()->create(['stock' => 5]);

        $this->post(route('cart.items.store', $product), ['quantity' => 1])
            ->assertSessionHasErrors([
                'product' => 'Este producto no está disponible para compra.',
            ])
            ->assertSessionMissing('cart.items.'.$product->id);
    }

    public function test_cart_items_include_current_product_information(): void
    {
        $product = Product::factory()->create(['price' => 9900]);
        $product->authors()->attach(Author::factory()->create(['name' => 'María Ejemplo']));

        $this->withSession(['cart.items' => [$product->id => 1]])
            ->get(route('cart.index'))
            ->assertInertia(
                fn (AssertableInertia $page) => $page
                    ->where('cart.items.0.title', $product->title)
                    ->where('cart.items.0.author', 'María Ejemplo')
                    ->where('cart.items.0.price', 9900)
                    ->where('cart.items.0.href', route('books.show', $product, absolute: false))
            );
    }
}
