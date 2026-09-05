<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\Publisher;
use Database\Factories\Concerns\CyclesThroughPool;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    use CyclesThroughPool;

    protected $model = Product::class;

    /**
     * El título se toma del producto cartesiano de estas dos listas: 576
     * combinaciones verosímiles, sin escribir a mano cientos de títulos.
     *
     * @var array<int, string>
     */
    private const TITLE_OPENINGS = [
        'La Palabra', 'El Evangelio', 'Camino', 'Senderos', 'La oración', 'El silencio',
        'Memoria', 'La alegría', 'Semillas', 'El pan', 'La mesa', 'Raíces', 'Signos',
        'La escucha', 'El rostro', 'Horizonte', 'La casa', 'Umbrales', 'La lámpara',
        'Cartas', 'Itinerario', 'La fuente', 'Huellas', 'El don',
    ];

    /** @var array<int, string> */
    private const TITLE_TAILS = [
        'que nos reúne', 'de la esperanza', 'para cada día', 'en tiempos de prueba',
        'del corazón', 'de la comunidad', 'que enseña', 'que se comparte',
        'en la vida cotidiana', 'para el camino', 'de la misericordia', 'en clave de fe',
        'para catequistas', 'del Adviento', 'en la escuela', 'de los más pequeños',
        'según san Pablo', 'que no se apaga', 'para la familia', 'en la Pascua',
        'de la Iglesia joven', 'para el domingo', 'de cada mañana', 'que abre caminos',
    ];

    /** @var array<int, string> */
    private const SUMMARIES = [
        'Un recorrido en textos breves para leer despacio y volver sobre lo leído.',
        'Material probado en grupos parroquiales, con propuestas para trabajar en comunidad.',
        'Una lectura accesible que no renuncia a la hondura de la tradición.',
        'Páginas para acompañar la oración personal a lo largo del año litúrgico.',
        'Un itinerario en etapas, pensado para el trabajo con grupos.',
        'Claves de lectura y preguntas que ayudan a llevar el texto a la vida diaria.',
    ];

    /** @var array<int, string> */
    private const BODY_SENTENCES = [
        'El libro parte de la experiencia cotidiana y la pone en diálogo con la Escritura.',
        'Cada capítulo se cierra con preguntas para la reflexión personal o en grupo.',
        'La edición incluye referencias bíblicas al margen y un índice temático.',
        'El lenguaje es sencillo y sirve tanto para la lectura personal como para la formación.',
        'Las propuestas de trabajo están graduadas y pueden adaptarse a distintas edades.',
        'Al final se ofrece una guía con celebraciones para los tiempos fuertes del año.',
    ];

    /**
     * Aviso que acompaña a todo el contenido generado, para que en el sitio
     * nunca se confunda una ficha de demostración con el catálogo oficial.
     */
    public const DEMO_NOTICE = 'Ficha de demostración: los datos de esta edición son '
        .'ficticios y se reemplazarán por los del catálogo oficial.';

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sequence = self::nextSequence();
        $title = self::fromPool(self::titles(), $sequence);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'short_description' => $this->faker->randomElement(self::SUMMARIES),
            'description' => $this->description(),
            'isbn' => self::isbn($sequence),

            // Precios de librería en pesos chilenos, redondeados a la centena
            // como en el punto de venta.
            'price' => $this->faker->numberBetween(59, 349) * 100,

            'pages' => $this->faker->numberBetween(48, 480),
            'dimensions' => $this->faker->randomElement([
                '13 × 20 cm', '14 × 21 cm', '15 × 23 cm', '17 × 24 cm', '21 × 27 cm',
            ]),
            // Con existencias por defecto: el quiebre de stock es un estado
            // que se pide a propósito con outOfStock().
            'stock' => $this->faker->numberBetween(1, 60),
            'active' => true,
            'featured' => false,
            'new_release' => false,
            'category_id' => Category::factory(),
            'publisher_id' => Publisher::factory(),
            'collection_id' => Collection::factory(),
            'published_at' => $this->faker->dateTimeBetween('-6 years', 'now')->format('Y-m-d'),
        ];
    }

    public function featured(): static
    {
        return $this->state(['featured' => true]);
    }

    /**
     * Novedad. Además de la marca, la fecha de edición debe ser reciente: el
     * listado de novedades ordena por ella.
     */
    public function newRelease(): static
    {
        return $this->state(fn (): array => [
            'new_release' => true,
            'published_at' => $this->faker->dateTimeBetween('-8 months', 'now')->format('Y-m-d'),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(['active' => false]);
    }

    public function outOfStock(): static
    {
        return $this->state(['stock' => 0]);
    }

    public function lowStock(): static
    {
        return $this->state(fn (): array => [
            'stock' => $this->faker->numberBetween(1, (int) config('paulinas.catalog.low_stock_threshold')),
        ]);
    }

    /**
     * Producto que no es un libro (tarjetería, imágenes, artículos de regalo):
     * ni ISBN ni número de páginas.
     */
    public function withoutIsbn(): static
    {
        return $this->state(['isbn' => null, 'pages' => null]);
    }

    /**
     * @return array<int, string>
     */
    private static function titles(): array
    {
        static $titles = null;

        return $titles ??= collect(self::TITLE_OPENINGS)
            ->crossJoin(self::TITLE_TAILS)
            ->map(fn (array $pair) => implode(' ', $pair))
            ->all();
    }

    private function description(): string
    {
        $body = $this->faker->randomElements(self::BODY_SENTENCES, 3);

        return implode(' ', $body)."\n\n".self::DEMO_NOTICE;
    }

    /**
     * ISBN-13 ficticio, con dígito de control válido, en el prefijo chileno
     * 978-956. El registrante 99 no corresponde a una editorial real.
     */
    private static function isbn(int $sequence): string
    {
        $publication = str_pad((string) ($sequence % 10000), 4, '0', STR_PAD_LEFT);
        $body = '97895699'.$publication;

        return "978-956-99-{$publication}-".self::checkDigit($body);
    }

    /**
     * Dígito de control del ISBN-13: pesos 1 y 3 alternados sobre los 12
     * primeros dígitos.
     */
    private static function checkDigit(string $body): int
    {
        $sum = 0;

        foreach (str_split($body) as $position => $digit) {
            $sum += ((int) $digit) * ($position % 2 === 0 ? 1 : 3);
        }

        return (10 - ($sum % 10)) % 10;
    }
}
