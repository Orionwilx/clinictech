<?php

use App\Http\Controllers\Admin\AreaController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\EquipmentCatalogController;
use App\Http\Controllers\Admin\EquipmentCategoryController;
use App\Http\Controllers\Admin\EquipmentController;
use App\Http\Controllers\Admin\EquipmentModelController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\TechnicianController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WorkOrderController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\EquipmentController as ClientEquipmentController;
use App\Http\Controllers\Client\TechnicianController as ClientTechnicianController;
use App\Http\Controllers\Client\WorkOrderController as ClientWorkOrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Technician\DashboardController as TechDashboardController;
use App\Http\Controllers\Technician\WorkOrderController as TechWorkOrderController;
use App\Http\Controllers\Technician\WorkOrderPhotoController;
use App\Models\Client;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Pantalla para usuarios desactivados (EnsureUserIsActive los redirige aquí tras cerrar su sesión).
Route::get('/cuenta-inactiva', fn () => view('auth.inactive'))->name('account.inactive');

Route::get('/dashboard', function () {
    $clients = Client::orderBy('name')->get();

    return view('dashboard', compact('clients'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/notifications/read-all', function () {
        auth()->user()->unreadNotifications->markAsRead();

        return response()->noContent();
    })->name('notifications.read-all');
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

    Route::get('equipment/{equipment}/pdf', [EquipmentController::class, 'pdf'])->name('equipment.pdf');
    Route::patch('equipment/{equipment}/toggle-active', [EquipmentController::class, 'toggleActive'])->name('equipment.toggle-active');
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
    Route::post('work_orders/{work_order}/approve-request', [WorkOrderController::class, 'approveRequest'])->name('work_orders.approve-request');
    Route::post('work_orders/{work_order}/reject-request', [WorkOrderController::class, 'rejectRequest'])->name('work_orders.reject-request');
    Route::post('work_orders/{work_order}/approve-work', [WorkOrderController::class, 'approveWork'])->name('work_orders.approve-work');
    Route::post('work_orders/{work_order}/reject-work', [WorkOrderController::class, 'rejectWork'])->name('work_orders.reject-work');
    Route::post('work_orders/{work_order}/send-to-client', [WorkOrderController::class, 'sendToClient'])->name('work_orders.send-to-client');
    // Evidencias fotográficas (el admin llena OT y anexa fotos que tomó el técnico).
    Route::post('work_orders/{work_order}/photos', [WorkOrderController::class, 'storePhoto'])->name('work_orders.photos.store');
    Route::delete('work_orders/{work_order}/photos/{photo}', [WorkOrderController::class, 'destroyPhoto'])->name('work_orders.photos.destroy');
    // Acciones rápidas y masivas desde la lista.
    Route::post('work_orders/batch', [WorkOrderController::class, 'batch'])->name('work_orders.batch');
    Route::post('work_orders/{work_order}/advance', [WorkOrderController::class, 'advance'])->name('work_orders.advance');
    Route::post('work_orders/{work_order}/regress', [WorkOrderController::class, 'regress'])->name('work_orders.regress');
    Route::post('work_orders/{work_order}/assign', [WorkOrderController::class, 'assign'])->name('work_orders.assign');
    Route::resource('work_orders', WorkOrderController::class);

    // Catálogo maestro de equipos: categorías, marcas y modelos.
    Route::resource('equipment_categories', EquipmentCategoryController::class)->except('show');
    Route::resource('brands', BrandController::class)->except('show');
    Route::resource('equipment_models', EquipmentModelController::class)->except('show');

    // Catálogos de opciones (subtareas / accesorios / especialidades).
    Route::prefix('equipment_catalogs')->name('equipment_catalogs.')
        ->whereIn('catalog', array_keys(EquipmentCatalogController::CATALOGS))
        ->group(function () {
            Route::get('{catalog?}', [EquipmentCatalogController::class, 'index'])->name('index');
            Route::post('{catalog}', [EquipmentCatalogController::class, 'store'])->name('store');
            Route::put('{catalog}/{item}', [EquipmentCatalogController::class, 'update'])->name('update');
            Route::delete('{catalog}/{item}', [EquipmentCatalogController::class, 'destroy'])->name('destroy');
        });

    // Reportes.
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::post('export', [ReportController::class, 'export'])->name('export');
        Route::get('{report}/download', [ReportController::class, 'download'])->name('download');
        Route::get('indicators', [ReportController::class, 'indicators'])->name('indicators');
    });
});

// Panel cliente — acceso segregado por client_id
Route::middleware(['auth', 'role:cliente', 'client.profile'])
    ->prefix('client')
    ->name('client.')
    ->group(function () {
        Route::get('dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');
        Route::get('equipment/{equipment}/pdf', [ClientEquipmentController::class, 'pdf'])->name('equipment.pdf');
        Route::resource('equipment', ClientEquipmentController::class)->only(['index', 'show']);
        Route::get('work_orders/{work_order}/pdf', [ClientWorkOrderController::class, 'pdf'])->name('work_orders.pdf');
        Route::resource('work_orders', ClientWorkOrderController::class)->only(['index', 'show', 'create', 'store']);
        Route::get('technicians', [ClientTechnicianController::class, 'index'])->name('technicians.index');
    });

// Panel técnico
Route::middleware(['auth', 'role:tecnico', 'technician.profile'])
    ->prefix('technician')
    ->name('technician.')
    ->group(function () {
        Route::get('dashboard', [TechDashboardController::class, 'index'])->name('dashboard');
        Route::post('work_orders/{work_order}/submit', [TechWorkOrderController::class, 'submit'])->name('work_orders.submit');
        // Evidencias fotográficas (subida AJAX inmediata + borrado).
        Route::post('work_orders/{work_order}/photos', [WorkOrderPhotoController::class, 'store'])->name('work_orders.photos.store');
        Route::delete('work_orders/{work_order}/photos/{photo}', [WorkOrderPhotoController::class, 'destroy'])->name('work_orders.photos.destroy');
        Route::resource('work_orders', TechWorkOrderController::class)->only(['index', 'show', 'update']);
    });

require __DIR__.'/auth.php';
