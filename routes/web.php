<?php

use App\Http\Controllers\Public\AboutController;
use App\Http\Controllers\Public\AuthorController;
use App\Http\Controllers\Public\BookController;
use App\Http\Controllers\Public\BranchController;
use App\Http\Controllers\Public\CartController;
use App\Http\Controllers\Public\CartItemController;
use App\Http\Controllers\Public\CategoryController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\PostController;
use App\Http\Controllers\Public\ResourceController;
use App\Http\Controllers\Public\RobotsController;
use App\Http\Controllers\Public\SitemapController;
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
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', RobotsController::class)->name('robots');

Route::get('/quienes-somos', AboutController::class)->name('about');
Route::redirect('/nuestra-mision', '/quienes-somos#nuestra-mision', 301);
Route::get('/contacto', [ContactController::class, 'index'])->name('contact.index');
Route::post('/contacto', [ContactController::class, 'store'])->middleware('throttle:6,1')->name('contact.store');
Route::get('/librerias', [BranchController::class, 'index'])->name('branches.index');
Route::get('/librerias/{branch}', [BranchController::class, 'show'])->name('branches.show');

require __DIR__.'/admin.php';

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
