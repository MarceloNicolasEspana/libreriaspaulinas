<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * El orden importa: ProductSeeder necesita la taxonomía ya creada.
     *
     * AdminUserSeeder va primero por comodidad, para que el aviso con las
     * credenciales locales no quede sepultado bajo la salida del catálogo.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            CategorySeeder::class,
            PublisherSeeder::class,
            CollectionSeeder::class,
            AuthorSeeder::class,
            ProductSeeder::class,
            BranchSeeder::class,
            ResourceCategorySeeder::class,
            ResourceSeeder::class,
            PostSeeder::class,
        ]);
    }
}
