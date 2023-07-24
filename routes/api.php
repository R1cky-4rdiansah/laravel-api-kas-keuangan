<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Products;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Kas;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register'])->name('register');



Route::group(['middleware' => ['api', 'jwt.verify']], function(){
    Route::get('/produk', [Products::class, 'produk'])->name('produk');
    Route::post('/detail/{id}', [Products::class, 'detail'])->name('detail');
    Route::post('/simpan', [Products::class, 'simpan'])->name('simpan');
    Route::post('/update/{id}', [Products::class, 'update'])->name('update');
    Route::post('/hapus/{id}', [Products::class, 'hapus'])->name('hapus');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/profile', [AuthController::class, 'profile'])->name('profile');

    Route::get('/kas', [Kas::class, 'kas'])->name('kas');
    Route::post('/report_pemasukkan', [Kas::class, 'report_pemasukkan'])->name('report_pemasukkan');
    Route::post('/report_pengeluaran', [Kas::class, 'report_pengeluaran'])->name('report_pengeluaran');
    Route::post('/report_saldo', [Kas::class, 'report_saldo'])->name('report_saldo');
    Route::get('/kas/{id}', [Kas::class, 'detail'])->name('detail_kas');
    Route::post('/input_kas', [Kas::class, 'input_kas'])->name('input_kas');
    Route::post('/update_kas/{id}', [Kas::class, 'update_kas'])->name('update_kas');
    Route::post('/hapus_kas/{id}', [Kas::class, 'hapus_kas'])->name('hapus_kas');

    Route::get('/users', [AuthController::class, 'users'])->name('users');
    Route::get('/getUser/{id_user}', [AuthController::class, 'getUser'])->name('getUser');
    Route::post('/update_users/{id}', [AuthController::class, 'update_users'])->name('update_users');
    Route::post('/delete_users/{id}', [AuthController::class, 'delete_users'])->name('delete_users');
    Route::post('/ganti_password', [AuthController::class, 'ganti_password'])->name('ganti_password');
});