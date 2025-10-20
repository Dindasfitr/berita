<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\BeritaController;
use App\Http\Controllers\Api\DisukaiController;
use App\Http\Controllers\Api\TidakDisukaiController;
use App\Http\Controllers\Api\HistoryController;
use App\Http\Controllers\Api\KategoriController;

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']); // ditambahkan login

// User
Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{id_user}', [UserController::class, 'show']);
Route::put('/users/{id_user}', [UserController::class, 'update']);
Route::delete('/users/{id_user}', [UserController::class, 'destroy']);

// Kategori
Route::get('/kategori', [KategoriController::class, 'index']);
Route::get('/kategori/{id_kategori}', [KategoriController::class, 'show']);
Route::post('/kategori', [KategoriController::class, 'store']);
Route::put('/kategori/{id_kategori}', [KategoriController::class, 'update']);
Route::delete('/kategori/{id_kategori}', [KategoriController::class, 'destroy']);

// Berita
Route::get('/berita', [BeritaController::class, 'index']);
Route::get('/berita/{id_berita}', [BeritaController::class, 'show']);
Route::get('/berita/user/{id_user}', [BeritaController::class, 'getByUser']);

// Protected routes (butuh login + role)
// Penulis & Admin bisa tambah atau edit berita
// Route::middleware(['auth:sanctum', 'role:admin,penulis'])->group(function () {
    Route::post('/berita', [BeritaController::class, 'store']);
    Route::post('/berita/{id_berita}', [BeritaController::class, 'update']); // edit menggunakan post
// });

// Hanya Admin bisa hapus berita
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::delete('/berita/{id_berita}', [BeritaController::class, 'destroy']);
});

// Likes
Route::get('/likes', [DisukaiController::class, 'index']);
Route::post('/likes', [DisukaiController::class, 'store']);
Route::put('/likes/{id_disukai}', [DisukaiController::class, 'update']);
Route::delete('/likes/{id_disukai}', [DisukaiController::class, 'destroy']);
Route::get('/likes/true', [DisukaiController::class, 'getTrueLikes']);
Route::get('/likes/false', [DisukaiController::class, 'getFalseLikes']);

// History
Route::get('/history', [HistoryController::class, 'index']);
Route::post('/history', [HistoryController::class, 'store']);
Route::get('/history/{id_history}', [HistoryController::class, 'show']);
Route::get('/history/user/{id_user}', [HistoryController::class, 'getByUser']);
Route::delete('/history/{id_history}', [HistoryController::class, 'destroy']);
