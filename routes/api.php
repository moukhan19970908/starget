<?php

use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ContractController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DictionaryController;
use App\Http\Controllers\Api\DriverController;
use App\Http\Controllers\Api\OwnerController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TransportationController;
use App\Http\Controllers\Api\VehicleController;
use Illuminate\Support\Facades\Route;

// Auth routes (public)
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::get('/auth/users', [AuthController::class, 'users']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('can:dashboard.view');
    Route::get('/dashboard/export', [DashboardController::class, 'export'])->middleware('can:dashboard.view');

    // Applications
    Route::middleware('can:applications.view')->group(function () {
        Route::get('/applications', [ApplicationController::class, 'index']);
        Route::get('/applications/{application}', [ApplicationController::class, 'show']);
    });
    Route::post('/applications', [ApplicationController::class, 'store'])->middleware('can:applications.create');
    Route::put('/applications/{application}', [ApplicationController::class, 'update'])->middleware('can:applications.edit');
    Route::put('/applications/{application}/status', [ApplicationController::class, 'changeStatus'])->middleware('can:applications.change_status');

    // Application stops
    Route::middleware('can:applications.edit')->group(function () {
        Route::post('/applications/{application}/stops', [ApplicationController::class, 'storeStop']);
        Route::put('/applications/{application}/stops/{stop}', [ApplicationController::class, 'updateStop']);
        Route::delete('/applications/{application}/stops/{stop}', [ApplicationController::class, 'destroyStop']);
    });

    // Transportations
    Route::post('/applications/{application}/transportations', [TransportationController::class, 'store'])->middleware('can:transportations.create');
    Route::middleware('can:transportations.view')->group(function () {
        Route::get('/transportations', [TransportationController::class, 'index']);
        Route::get('/transportations/{transportation}', [TransportationController::class, 'show']);
    });
    Route::put('/transportations/{transportation}', [TransportationController::class, 'update'])->middleware('can:transportations.edit');
    Route::post('/transportations/{transportation}/call-photo', [TransportationController::class, 'uploadCallPhoto'])->middleware('can:transportations.edit');
    Route::post('/transportations/{transportation}/complete', [TransportationController::class, 'complete'])->middleware('can:transportations.complete');
    Route::get('/transportations/{transportation}/export', [TransportationController::class, 'export'])->middleware('can:transportations.view');
    Route::post('/transportations/{transportation}/documents', [TransportationController::class, 'uploadDocument'])->middleware('can:transportations.edit');

    // Clients
    Route::middleware('can:clients.view')->group(function () {
        Route::get('/clients', [ClientController::class, 'index']);
        Route::get('/clients/{client}', [ClientController::class, 'show']);
        Route::get('/clients/{client}/contracts', [ClientController::class, 'contracts']);
    });
    Route::post('/clients', [ClientController::class, 'store'])->middleware('can:clients.create');
    Route::put('/clients/{client}', [ClientController::class, 'update'])->middleware('can:clients.edit');
    Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->middleware('can:clients.delete');

    // Contracts
    Route::middleware('can:contracts.view')->group(function () {
        Route::get('/contracts', [ContractController::class, 'index']);
        Route::get('/contracts/{contract}', [ContractController::class, 'show']);
    });
    Route::post('/contracts', [ContractController::class, 'store'])->middleware('can:contracts.create');
    Route::put('/contracts/{contract}', [ContractController::class, 'update'])->middleware('can:contracts.edit');
    Route::delete('/contracts/{contract}', [ContractController::class, 'destroy'])->middleware('can:contracts.edit');

    // Suppliers
    Route::middleware('can:suppliers.view')->group(function () {
        Route::get('/suppliers', [SupplierController::class, 'index']);
        Route::get('/suppliers/{supplier}', [SupplierController::class, 'show']);
        Route::get('/suppliers/{supplier}/contracts', [SupplierController::class, 'contracts']);
    });
    Route::post('/suppliers', [SupplierController::class, 'store'])->middleware('can:suppliers.create');
    Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->middleware('can:suppliers.edit');

    // Vehicles
    Route::get('/vehicles/search', [VehicleController::class, 'search'])->middleware('can:vehicles.view');
    Route::middleware('can:vehicles.view')->group(function () {
        Route::get('/vehicles', [VehicleController::class, 'index']);
        Route::get('/vehicles/{vehicle}', [VehicleController::class, 'show']);
    });
    Route::post('/vehicles', [VehicleController::class, 'store'])->middleware('can:vehicles.create');
    Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update'])->middleware('can:vehicles.edit');

    // Drivers
    Route::get('/drivers/search', [DriverController::class, 'search'])->middleware('can:drivers.view');
    Route::middleware('can:drivers.view')->group(function () {
        Route::get('/drivers', [DriverController::class, 'index']);
        Route::get('/drivers/{driver}', [DriverController::class, 'show']);
    });
    Route::post('/drivers', [DriverController::class, 'store'])->middleware('can:drivers.create');
    Route::put('/drivers/{driver}', [DriverController::class, 'update'])->middleware('can:drivers.edit');
    Route::post('/drivers/{driver}/documents', [DriverController::class, 'addDocument'])->middleware('can:drivers.edit');

    // Owners
    Route::get('/owners', [OwnerController::class, 'index'])->middleware('can:vehicles.view');
    Route::post('/owners', [OwnerController::class, 'store'])->middleware('can:owners.create');
    Route::get('/owners/{owner}', [OwnerController::class, 'show'])->middleware('can:vehicles.view');
    Route::put('/owners/{owner}', [OwnerController::class, 'update'])->middleware('can:owners.edit');

    // Tasks
    Route::get('/tasks', [TaskController::class, 'index'])->middleware('can:tasks.view');
    Route::post('/tasks', [TaskController::class, 'store'])->middleware('can:tasks.create');
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->middleware('can:tasks.view');
    Route::put('/tasks/{task}/close', [TaskController::class, 'close'])->middleware('can:tasks.close');

    // Dictionaries
    Route::prefix('dict')->group(function () {
        Route::get('/cities', [DictionaryController::class, 'cities']);
        Route::get('/currencies', [DictionaryController::class, 'currencies']);
        Route::get('/loading-types', [DictionaryController::class, 'loadingTypes']);
        Route::get('/vehicle-types', [DictionaryController::class, 'vehicleTypes']);
        Route::get('/document-types', [DictionaryController::class, 'documentTypes']);
        Route::get('/stop-types', [DictionaryController::class, 'stopTypes']);
        Route::get('/refusal-reasons', [DictionaryController::class, 'refusalReasons']);
    });
});
