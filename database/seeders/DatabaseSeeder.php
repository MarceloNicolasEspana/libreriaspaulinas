<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * El orden importa: ProductSeeder necesita la taxonomía ya creada.
     */
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            PublisherSeeder::class,
            CollectionSeeder::class,
            AuthorSeeder::class,
            ProductSeeder::class,
            ResourceCategorySeeder::class,
            ResourceSeeder::class,
            PostSeeder::class,
        ]);
    }
}
