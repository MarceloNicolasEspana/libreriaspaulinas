<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Autoría de los productos.
 *
 * Es muchos-a-muchos en las dos direcciones: un título puede firmarlo un equipo
 * y un autor firma varios títulos. "sort_order" conserva el orden en que la
 * portada los enumera, que no es alfabético.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('author_product', function (Blueprint $table) {
            $table->foreignId('author_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->primary(['product_id', 'author_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('author_product');
    }
};
