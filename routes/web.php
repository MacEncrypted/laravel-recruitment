<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanyUserController;
use App\Http\Controllers\MyItemsController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {
    Route::resource('companies', CompanyController::class);

    // Routes dla użytkowników firmy
    Route::get('companies/{company}/users', [CompanyUserController::class, 'index'])
        ->name('companies.users.index');
    Route::post('companies/{company}/users', [CompanyUserController::class, 'attach'])
        ->name('companies.users.attach');
    Route::delete('companies/{company}/users/{user}', [CompanyUserController::class, 'detach'])
        ->name('companies.users.detach');

    // Magazyn firmy zalogowanego użytkownika
    Route::middleware('can:manageWarehouse,company')->group(function () {
        Route::get('companies/{company}/warehouse', [WarehouseController::class, 'index'])
            ->name('companies.warehouse.index');
        Route::post('companies/{company}/warehouse', [WarehouseController::class, 'store'])
            ->name('companies.warehouse.store');
        Route::get('companies/{company}/warehouse/{item}', [WarehouseController::class, 'show'])
            ->name('companies.warehouse.show');
        Route::post('companies/{company}/warehouse/{item}/assign', [WarehouseController::class, 'assign'])
            ->name('companies.warehouse.assign');
        Route::delete('companies/{company}/warehouse/{item}/assign', [WarehouseController::class, 'unassign'])
            ->name('companies.warehouse.unassign');
    });

    // Przedmioty zalogowanego użytkownika
    Route::get('my-items', [MyItemsController::class, 'index'])
        ->name('items.mine');
});

require __DIR__.'/auth.php';
