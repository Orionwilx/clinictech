<?php

use App\Http\Controllers\Admin\AreaController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\EquipmentController;
use App\Http\Controllers\Admin\EquipmentModelController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\TechnicianController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WorkOrderController;
use App\Http\Controllers\ProfileController;
use App\Models\Client;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $clients = Client::orderBy('name')->get();
    return view('dashboard', compact('clients'));
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

    // Áreas de trabajo (anidadas al cliente; se gestionan desde el hub).
    Route::post('clients/{client}/areas', [AreaController::class, 'store'])->name('clients.areas.store');
    Route::put('areas/{area}', [AreaController::class, 'update'])->name('areas.update');
    Route::delete('areas/{area}', [AreaController::class, 'destroy'])->name('areas.destroy');

    Route::put('equipment/{id}/restore', [EquipmentController::class, 'restore'])
        ->withTrashed()
        ->name('equipment.restore');
    Route::resource('equipment', EquipmentController::class)->parameters(['equipment' => 'equipment']);

    Route::put('technicians/{id}/restore', [TechnicianController::class, 'restore'])
        ->withTrashed()
        ->name('technicians.restore');
    Route::resource('technicians', TechnicianController::class);

    Route::get('work_orders/{work_order}/pdf', [WorkOrderController::class, 'pdf'])->name('work_orders.pdf');
    Route::put('work_orders/{id}/restore', [WorkOrderController::class, 'restore'])
        ->withTrashed()
        ->name('work_orders.restore');
    Route::resource('work_orders', WorkOrderController::class);

    // Catálogo de equipos: marcas y modelos.
    Route::resource('brands', BrandController::class)->except('show');
    Route::get('equipment_models/{equipment_model}/data', [EquipmentModelController::class, 'data'])->name('equipment_models.data');
    Route::resource('equipment_models', EquipmentModelController::class)->except('show');

    // Reportes.
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::post('export', [ReportController::class, 'export'])->name('export');
        Route::get('{report}/download', [ReportController::class, 'download'])->name('download');
        Route::get('indicators', [ReportController::class, 'indicators'])->name('indicators');
    });
});

require __DIR__.'/auth.php';
