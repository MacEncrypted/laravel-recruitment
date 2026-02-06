<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CompanyController;
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
});

require __DIR__.'/auth.php';
