<?php

use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\EquipmentController;
use App\Http\Controllers\Admin\EquipmentModelController;
use App\Http\Controllers\Admin\TechnicianController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WorkOrderController;
use App\Http\Controllers\ProfileController;
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

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::put('users/{id}/restore', [UserController::class, 'restore'])
        ->withTrashed()
        ->name('users.restore');
    Route::resource('users', UserController::class);

    Route::put('clients/{id}/restore', [ClientController::class, 'restore'])
        ->withTrashed()
        ->name('clients.restore');
    Route::resource('clients', ClientController::class);

    Route::put('equipment/{id}/restore', [EquipmentController::class, 'restore'])
        ->withTrashed()
        ->name('equipment.restore');
    Route::resource('equipment', EquipmentController::class)->parameters(['equipment' => 'equipment']);

    Route::put('technicians/{id}/restore', [TechnicianController::class, 'restore'])
        ->withTrashed()
        ->name('technicians.restore');
    Route::resource('technicians', TechnicianController::class);

    Route::put('work_orders/{id}/restore', [WorkOrderController::class, 'restore'])
        ->withTrashed()
        ->name('work_orders.restore');
    Route::resource('work_orders', WorkOrderController::class);

    // Catálogo de equipos: marcas y modelos.
    Route::resource('brands', BrandController::class)->except('show');
    Route::resource('equipment_models', EquipmentModelController::class)->except('show');
});

require __DIR__.'/auth.php';
