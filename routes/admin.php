<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RecordController;
use App\Http\Controllers\Admin\SessionController;
use App\Support\Admin\Catalog;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/admin/login', [SessionController::class, 'create'])->name('login');
    Route::post('/admin/login', [SessionController::class, 'store'])->name('login.store');
});
Route::post('/admin/logout', [SessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'can:manage-admin'])->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::prefix('{section}')->whereIn('section', array_keys(Catalog::SECTIONS))->group(function (): void {
        Route::get('/', [RecordController::class, 'index'])->name('records.index');
        Route::get('/crear', [RecordController::class, 'create'])->name('records.create');
        Route::post('/', [RecordController::class, 'store'])->name('records.store');
        Route::get('/{record}/editar', [RecordController::class, 'edit'])->whereNumber('record')->name('records.edit');
        Route::put('/{record}', [RecordController::class, 'update'])->whereNumber('record')->name('records.update');
        Route::delete('/{record}', [RecordController::class, 'destroy'])->whereNumber('record')->name('records.destroy');
    });
});
