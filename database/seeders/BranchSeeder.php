<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

/**
 * Librerías publicadas en el directorio oficial Paulinas Sector Chile de mayo de 2026.
 *
 * @see https://paulinas.cl/PAULINAS_Direcciones_Horarios_2026_MAYO_web.pdf
 */
class BranchSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->branches() as $branch) {
            Branch::firstOrCreate(['slug' => $branch['slug']], $branch);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function branches(): array
    {
        return [
            [
                'name' => 'Paulinas La Florida',
                'slug' => 'la-florida',
                'address' => 'Inés de Suárez 7639',
                'commune' => 'La Florida',
                'region' => 'Región Metropolitana de Santiago',
                'phone' => '+56 2 2255 0703',
                'whatsapp' => '+56 9 3558 4641',
                'email' => 'salaventas@paulinas.cl',
                'opening_hours' => [
                    ['days' => 'Lunes a viernes', 'periods' => ['09:00–13:00', '13:30–18:00']],
                    ['days' => 'Sábados', 'periods' => ['10:00–13:00']],
                ],
                'active' => true,
                'sort_order' => 10,
            ],
            [
                'name' => 'Paulinas Santiago Centro',
                'slug' => 'santiago-centro',
                'address' => 'Santo Domingo 951',
                'commune' => 'Santiago',
                'region' => 'Región Metropolitana de Santiago',
                'phone' => null,
                'whatsapp' => '+56 9 2036 4222',
                'email' => 'santiagosd@paulinas.cl',
                'opening_hours' => [
                    ['days' => 'Lunes', 'periods' => ['09:30–14:00', '14:30–18:05']],
                    ['days' => 'Martes a viernes', 'periods' => ['09:30–14:00', '14:30–17:35']],
                    ['days' => 'Sábados', 'periods' => ['09:30–13:05']],
                ],
                'active' => true,
                'sort_order' => 20,
            ],
            [
                'name' => 'Paulinas Concepción',
                'slug' => 'concepcion',
                'address' => 'O’Higgins 770, Edificio Amanecer, local 17',
                'commune' => 'Concepción',
                'region' => 'Región del Biobío',
                'phone' => '+56 41 222 3778',
                'whatsapp' => '+56 9 8937 9508',
                'email' => 'concepcionlib@paulinas.cl',
                'opening_hours' => [
                    ['days' => 'Lunes', 'periods' => ['10:00–17:30']],
                    ['days' => 'Martes a jueves', 'periods' => ['10:00–18:00']],
                    ['days' => 'Viernes', 'periods' => ['10:00–17:30']],
                    ['days' => 'Sábados', 'periods' => ['10:00–13:30']],
                ],
                'active' => true,
                'sort_order' => 30,
            ],
        ];
    }
}
