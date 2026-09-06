<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();

            // Texto de ficha y de tarjeta. Ambos opcionales: un producto puede
            // darse de alta con lo mínimo y completarse después.
            $table->string('short_description', 300)->nullable();
            $table->text('description')->nullable();

            /*
             * ISBN-13 con separadores ("978-956-9910-42-7" = 17 caracteres).
             * Es nullable porque el catálogo también incluye artículos que no
             * son libros (rosarios, imágenes, tarjetería), y único porque dos
             * fichas con el mismo ISBN son la misma edición duplicada. Tanto
             * MySQL como SQLite permiten varios NULL bajo un índice único.
             */
            $table->string('isbn', 20)->nullable()->unique();

            /*
             * DECIMAL, nunca FLOAT: un precio es un valor exacto y el binario
             * flotante no puede representar decimales exactos.
             *
             * Se guarda en la unidad mayor de la moneda (12990.00 = $12.990).
             * App\Support\Money hace la conversión a la unidad menor que espera
             * el frontend.
             */
            $table->decimal('price', 10, 2);

            $table->unsignedSmallInteger('pages')->nullable();
            $table->string('dimensions', 60)->nullable();
            $table->unsignedInteger('stock')->default(0);

            // Visible en el sitio. Un producto agotado sigue activo: la ficha se
            // muestra y el estado pasa a "agotado".
            $table->boolean('active')->default(true);
            $table->boolean('featured')->default(false);
            $table->boolean('new_release')->default(false);

            /*
             * Las tres relaciones son nullable con nullOnDelete: reorganizar la
             * taxonomía no puede arrastrar productos del catálogo. Además, no
             * todo producto pertenece a una colección.
             */
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('publisher_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('collection_id')->nullable()->constrained()->nullOnDelete();

            // Fecha de edición, no de publicación en el sitio (para eso está
            // "active"). Sin hora: el dato editorial es el día.
            $table->date('published_at')->nullable();

            $table->timestamps();

            // Portada: destacados y novedades siempre se filtran por activos.
            $table->index(['active', 'featured']);
            $table->index(['active', 'new_release']);
            $table->index(['active', 'published_at']);

            // Listados de sección y de editorial.
            $table->index(['category_id', 'active']);
            $table->index(['publisher_id', 'active']);
        });

        /*
         * Búsqueda por texto. FULLTEXT existe en MySQL pero no en SQLite, que es
         * el motor de los tests; el buscador debe degradar a LIKE cuando el
         * índice no está disponible.
         */
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            Schema::table('products', function (Blueprint $table) {
                $table->fullText(['title', 'short_description', 'description'], 'products_search_fulltext');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
