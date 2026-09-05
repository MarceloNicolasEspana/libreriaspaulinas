<?php

use App\Http\Controllers\Public\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Sitio público
|--------------------------------------------------------------------------
|
| Las páginas del sitio se resuelven con Inertia, sin API REST intermedia.
| Las secciones (catálogo, librerías, recursos) se agregarán en fases
| posteriores usando este mismo esquema de rutas con nombre.
|
*/

Route::get('/', HomeController::class)->name('home');

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
