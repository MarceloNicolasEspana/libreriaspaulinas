<?php

namespace App\Support\Catalog;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

/**
 * Búsqueda de texto del catálogo.
 *
 * Busca en título, descripciones, ISBN, nombre de autor y nombre de colección.
 * Todo ocurre en la base de datos: el navegador nunca recibe el catálogo
 * completo para filtrarlo por su cuenta.
 *
 * La consulta se resuelve con LIKE y no con el índice FULLTEXT que crea la
 * migración de "products". El índice cubre solo las tres columnas de texto de
 * la tabla, y la búsqueda debe alcanzar también al autor, al ISBN y a la
 * colección, que viven en otras tablas; mantener dos caminos distintos (MySQL
 * con MATCH, SQLite con LIKE) haría que los tests ejercitaran un buscador que
 * no es el que corre en producción. El índice queda disponible para cuando el
 * catálogo crezca lo suficiente como para mover la búsqueda a un motor propio.
 */
final class ProductSearch
{
    /**
     * Palabras que se consideran por búsqueda. Cada una agrega una subconsulta,
     * así que el límite acota el costo de una frase larga pegada al buscador.
     */
    private const MAX_TERMS = 6;

    /**
     * Largo máximo del término. Más allá de esto no hay títulos que encontrar.
     */
    private const MAX_LENGTH = 80;

    /**
     * Mínimo de dígitos para tratar un término como ISBN. Por debajo de cuatro
     * ("Salmo 119") el número es parte del título, no un código.
     */
    private const MIN_ISBN_DIGITS = 4;

    /**
     * ISBN sin separadores, para que "9789569900427" encuentre a
     * "978-956-99-0042-7".
     */
    private const ISBN = "replace(replace(products.isbn, '-', ''), ' ', '')";

    /**
     * Restringe la consulta a los productos que coinciden con la búsqueda.
     *
     * Los términos se combinan con AND y cada uno con OR entre los campos:
     * "biblia niños" exige que ambas palabras aparezcan en alguna parte de la
     * ficha, que es lo que espera quien escribe dos palabras.
     *
     * @param  Builder<Product>  $query
     */
    public static function apply(Builder $query, string $search): void
    {
        foreach (self::terms($search) as $term) {
            $query->where(fn (Builder $scoped) => self::matchTerm($scoped, $term));
        }
    }

    /**
     * Ordena por cercanía a lo buscado.
     *
     * El puntaje se calcula en SQL para no traer filas que después habría que
     * ordenar en PHP. Es deliberadamente simple: coincidencia de ISBN, título
     * exacto, título que empieza por el término y título que lo contiene.
     *
     * @param  Builder<Product>  $query
     */
    public static function applyRelevance(Builder $query, string $search): void
    {
        $term = self::normalize($search);
        $isbn = self::isbnDigits($search);

        $query->orderByRaw(
            'case'
            .' when '.self::ISBN.' = ? then 4'
            .' when products.title = ? then 3'
            .' when products.title like ? then 2'
            .' when products.title like ? then 1'
            .' else 0 end desc',
            [$isbn, $term, $term.'%', '%'.$term.'%'],
        );
    }

    /**
     * Palabras de la búsqueda, ya normalizadas.
     *
     * @return array<int, string>
     */
    public static function terms(string $search): array
    {
        return collect(preg_split('/\s+/', self::normalize($search), flags: PREG_SPLIT_NO_EMPTY) ?: [])
            ->unique()
            ->take(self::MAX_TERMS)
            ->values()
            ->all();
    }

    /**
     * @param  Builder<Product>  $query
     */
    private static function matchTerm(Builder $query, string $term): void
    {
        $like = '%'.$term.'%';

        $query
            ->where('products.title', 'like', $like)
            ->orWhere('products.short_description', 'like', $like)
            ->orWhere('products.description', 'like', $like)
            // whereHas resuelve la relación con una subconsulta: no hay N+1 ni
            // JOIN que multiplique filas y descuadre la paginación.
            ->orWhereHas('authors', fn (Builder $authors) => $authors->where('authors.name', 'like', $like))
            ->orWhereHas('collection', fn (Builder $collections) => $collections->where('collections.name', 'like', $like));

        $isbn = self::isbnDigits($term);

        if ($isbn !== '') {
            $query->orWhereRaw(self::ISBN.' like ?', ['%'.$isbn.'%']);
        }
    }

    /**
     * Deja el término listo para usarse dentro de un LIKE.
     *
     * Se quitan los comodines de SQL: escaparlos exigiría una cláusula ESCAPE
     * que MySQL y SQLite tratan distinto, y un "%" escrito en el buscador es
     * casi siempre un dedazo, no una intención.
     */
    private static function normalize(string $search): string
    {
        $clean = str_replace(['%', '_'], ' ', $search);

        return trim(preg_replace('/\s+/', ' ', mb_substr($clean, 0, self::MAX_LENGTH)) ?? '');
    }

    /**
     * Dígitos del término si parece un ISBN; cadena vacía si no.
     */
    private static function isbnDigits(string $term): string
    {
        $digits = preg_replace('/\D+/', '', $term) ?? '';

        return mb_strlen($digits) >= self::MIN_ISBN_DIGITS ? $digits : '';
    }
}
