<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Secciones del catálogo.
 *
 * La jerarquía es de profundidad libre (parent_id apunta a la misma tabla),
 * aunque el sitio muestra por ahora dos niveles: sección y subsección.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            /*
             * Al eliminar una sección padre, sus hijas ascienden a raíz en vez de
             * desaparecer: perder una rama entera del catálogo por un borrado
             * nunca es el comportamiento deseado.
             */
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();

            $table->string('image')->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            // Listado del menú y de la portada: filtrar por activas y ordenar.
            $table->index(['active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
