<?php

use App\Http\Controllers\Public\AuthorController;
use App\Http\Controllers\Public\BookController;
use App\Http\Controllers\Public\CartController;
use App\Http\Controllers\Public\CartItemController;
use App\Http\Controllers\Public\CategoryController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\PostController;
use App\Http\Controllers\Public\ResourceController;
use App\Http\Controllers\Public\SectionPlaceholderController;
use App\Support\DemoContent;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Sitio público
|--------------------------------------------------------------------------
|
| Las páginas del sitio se resuelven con Inertia, sin API REST intermedia.
|
*/

Route::get('/', HomeController::class)->name('home');

Route::get('/recursos', [ResourceController::class, 'index'])->name('resources.index');
Route::get('/recursos/{resource}/descargar', [ResourceController::class, 'download'])->name('resources.download');
Route::get('/recursos/{resource}', [ResourceController::class, 'show'])->name('resources.show');
Route::get('/novedades', [PostController::class, 'index'])->name('posts.index');
Route::get('/novedades/{post}', [PostController::class, 'show'])->name('posts.show');

/*
|--------------------------------------------------------------------------
| Catálogo
|--------------------------------------------------------------------------
|
| Los tres modelos se enlazan por slug (ver getRouteKeyName), de modo que las
| URL sean legibles: /libros/la-palabra-que-nos-reune.
|
*/

Route::get('/libros', [BookController::class, 'index'])->name('books.index');
Route::get('/libros/{product}', [BookController::class, 'show'])->name('books.show');
Route::get('/categorias/{category}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/autores/{author}', [AuthorController::class, 'show'])->name('authors.show');

Route::get('/carrito', CartController::class)->name('cart.index');
Route::post('/carrito/items/{product}', [CartItemController::class, 'store'])->name('cart.items.store');
Route::patch('/carrito/items/{product}', [CartItemController::class, 'update'])->name('cart.items.update');
Route::delete('/carrito/items/{product}', [CartItemController::class, 'destroy'])->name('cart.items.destroy');

/*
 * Secciones anunciadas en la navegación y enlazadas desde la portada que
 * todavía no tienen contenido propio.
 *
 * Se registran explícitamente (y no con un comodín) para que una URL inexistente
 * siga devolviendo 404. A medida que cada sección reciba su controlador propio,
 * se retira de esta lista: las rutas ya registradas arriba se descartan solas.
 */
$claimed = collect(Route::getRoutes()->getRoutes())
    ->map(fn ($route) => '/'.ltrim($route->uri(), '/'))
    ->all();

$pendingSections = collect(config('navigation.primary'))
    ->concat(collect(config('navigation.footer'))->flatMap(fn (array $column) => $column['links']))
    ->pluck('href')
    ->concat(array_keys(DemoContent::placeholderLinks()))
    ->unique()
    ->reject(fn (string $path) => str_starts_with($path, '/recursos'))
    ->reject(fn (string $path) => in_array($path, $claimed, strict: true));

foreach ($pendingSections as $path) {
    Route::get($path, SectionPlaceholderController::class);
}

/*
|--------------------------------------------------------------------------
| Panel administrativo
|--------------------------------------------------------------------------
|
| Preparado para la fase de administración. Las rutas se registrarán aquí
| bajo el prefijo "admin." cuando exista autenticación.
|
*/

// Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(base_path('routes/admin.php'));
