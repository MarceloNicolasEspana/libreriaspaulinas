import { router } from '@inertiajs/vue3';

/** Ruta del catálogo. Es la única página que interpreta estos parámetros. */
const CATALOG = '/libros';

/** Orden por defecto: se omite de la URL para no ensuciarla. */
const DEFAULT_SORT = 'relevancia';

/**
 * Navegación del catálogo.
 *
 * El estado de los filtros no vive en el componente: vive en la query string y
 * lo resuelve Laravel. Cada cambio es una navegación de Inertia hacia la misma
 * página con otros parámetros, de modo que la URL siempre describe lo que se
 * está viendo y se puede compartir, marcar o recorrer con el botón "atrás".
 *
 * @param {() => Record<string, unknown>} current  Filtros vigentes (props.filters).
 */
export function useCatalogFilters(current) {
    /**
     * Combina los filtros vigentes con los cambios pedidos y limpia el
     * resultado.
     *
     * Siempre se vuelve a la primera página: quedarse en la página 4 después de
     * acotar la búsqueda deja al visitante fuera de rango, mirando una lista
     * vacía que sí tiene resultados.
     */
    function merge(changes) {
        const next = { ...current(), ...changes, page: null };

        return Object.fromEntries(
            Object.entries(next).filter(
                ([key, value]) =>
                    value !== null &&
                    value !== undefined &&
                    value !== '' &&
                    // El orden por defecto es el que aplica sin parámetro.
                    !(key === 'orden' && value === DEFAULT_SORT),
            ),
        );
    }

    /**
     * URL resultante de aplicar unos cambios.
     *
     * Las opciones de la barra son enlaces de verdad y no botones: así el
     * catálogo filtrado es rastreable y se puede abrir en otra pestaña.
     */
    function href(changes) {
        const query = new URLSearchParams(
            Object.entries(merge(changes)).map(([key, value]) => [key, String(value)]),
        ).toString();

        return query === '' ? CATALOG : `${CATALOG}?${query}`;
    }

    /**
     * Navega aplicando unos cambios.
     *
     * preserveState mantiene viva la página: el panel de filtros no se cierra
     * ni pierde su posición de scroll mientras la cuadrícula se actualiza.
     * preserveScroll evita el salto al inicio, que sería desconcertante cuando
     * solo cambió una casilla de la barra lateral.
     */
    function apply(changes, options = {}) {
        router.get(CATALOG, merge(changes), {
            preserveState: true,
            preserveScroll: true,
            ...options,
        });
    }

    /** Enciende o apaga un valor de una faceta. */
    function toggle(key, value) {
        apply({ [key]: current()[key] === value ? null : value });
    }

    /** Quita un filtro. La clave puede traer varias separadas por coma. */
    function remove(key) {
        apply(Object.fromEntries(key.split(',').map((name) => [name, null])));
    }

    /** Deja solo el orden: es una preferencia de lectura, no un filtro. */
    function clear() {
        apply(
            Object.fromEntries(
                Object.keys(current())
                    .filter((key) => key !== 'orden')
                    .map((key) => [key, null]),
            ),
        );
    }

    /**
     * Una búsqueda nueva sí devuelve al inicio de la página: el listado cambia
     * por completo y quedarse a media altura escondería los primeros
     * resultados.
     */
    function search(term) {
        apply({ q: term === '' ? null : term }, { preserveScroll: false });
    }

    function sort(value) {
        apply({ orden: value });
    }

    /** Cuántos filtros hay puestos, sin contar el orden. */
    function activeCount() {
        return Object.entries(current()).filter(
            ([key, value]) => key !== 'orden' && value !== null && value !== undefined && value !== '',
        ).length;
    }

    return { href, apply, toggle, remove, clear, search, sort, activeCount };
}
