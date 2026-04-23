<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect('/login'));
Route::get('/login', fn() => view('auth.login'))->name('login');

// Protected pages (auth enforced client-side via localStorage token check)
Route::get('/dashboard',              fn() => view('dashboard'));
Route::get('/applications',           fn() => view('applications.index'));
Route::get('/applications/create',    fn() => view('applications.create'));
Route::get('/applications/{id}',      fn() => view('applications.show'));
Route::get('/transportations',        fn() => view('transportations.index'));
Route::get('/transportations/create', fn() => view('transportations.create'));
Route::get('/contracts',              fn() => view('contracts.index'));
Route::get('/clients',                fn() => view('clients.index'));
Route::get('/suppliers',              fn() => view('suppliers.index'));
Route::get('/suppliers/create',       fn() => view('suppliers.create'));
Route::get('/vehicles',               fn() => view('vehicles.index'));
Route::get('/vehicles/create',        fn() => view('vehicles.create'));
Route::get('/drivers',                fn() => view('drivers.index'));
Route::get('/drivers/create',         fn() => view('drivers.create'));

