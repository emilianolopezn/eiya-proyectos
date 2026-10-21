<?php

use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [SessionController::class, 'create'])->name('login');
    Route::post('/login', [SessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/clientes', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/clientes/crear', [ClientController::class, 'create'])->name('clients.create');
    Route::post('/clientes', [ClientController::class, 'store'])->name('clients.store');
    Route::get('/clientes/{client}', [ClientController::class, 'show'])->name('clients.show');
    Route::get('/clientes/{client}/editar', [ClientController::class, 'edit'])->name('clients.edit');
    Route::put('/clientes/{client}', [ClientController::class, 'update'])->name('clients.update');
    Route::get('/clientes/{client}/logo', [ClientController::class, 'logo'])->name('clients.logo');
    Route::get('/clientes/{client}/contactos/crear', [ContactController::class, 'create'])->name('contacts.create');
    Route::post('/clientes/{client}/contactos', [ContactController::class, 'store'])->name('contacts.store');
    Route::get('/clientes/{client}/contactos/{contact}/editar', [ContactController::class, 'edit'])->name('contacts.edit');
    Route::put('/clientes/{client}/contactos/{contact}', [ContactController::class, 'update'])->name('contacts.update');
    Route::delete('/clientes/{client}/contactos/{contact}', [ContactController::class, 'destroy'])->name('contacts.destroy');
    Route::get('/clientes/{client}/contactos/{contact}/foto', [ContactController::class, 'photo'])->name('contacts.photo');
    Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');
});
