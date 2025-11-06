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
use App\Http\Controllers\Api\UpgradeController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\BookmarkController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\AnalyticsController;

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']); // ditambahkan login

// User
Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{id_user}', [UserController::class, 'show']);
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/users/{id_user}', [UserController::class, 'update']);
});
Route::delete('/users/{id_user}', [UserController::class, 'destroy']);

// Kategori

Route::get('/kategori', [KategoriController::class, 'index']);
Route::get('/kategori/{id_kategori}', [KategoriController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/kategori', [KategoriController::class, 'store']);
    Route::put('/kategori/{id_kategori}', [KategoriController::class, 'update']);
    Route::delete('/kategori/{id_kategori}', [KategoriController::class, 'destroy']);
});



// Berita
Route::get('/berita/search', [BeritaController::class, 'search']);
Route::get('/berita', [BeritaController::class, 'index']);
Route::get('/berita/{id_berita}', [BeritaController::class, 'show']);
Route::get('/berita/user/{id_user}', [BeritaController::class, 'getByUser']);
Route::get('/berita/category/{id_kategori}', [BeritaController::class, 'getByCategory']);

// Protected routes (butuh login + role)
// Penulis & Admin bisa tambah atau edit berita
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/berita', [BeritaController::class, 'store']);
    Route::post('/berita/{id_berita}', [BeritaController::class, 'update']); // edit menggunakan put
    Route::patch('/berita/{id_berita}', [BeritaController::class, 'update']); // alternatif patch
});

// Hanya Admin bisa hapus berita
Route::middleware('auth:sanctum')->group(function () {
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

// Transaction
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/transaction', [TransactionController::class, 'transaction']);
});

// Upgrade
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/upgrade', [UpgradeController::class, 'upgrade']);
});

// Search
Route::get('/search', [SearchController::class, 'search']);
Route::get('/search/advanced', [SearchController::class, 'advancedSearch']);

// Bookmark
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/bookmarks', [BookmarkController::class, 'index']);
    Route::post('/bookmarks', [BookmarkController::class, 'store']);
    Route::delete('/bookmarks/{id_bookmark}', [BookmarkController::class, 'destroy']);
});

// Notification
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::put('/notifications/{id_notification}/read', [NotificationController::class, 'markAsRead']);
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id_notification}', [NotificationController::class, 'destroy']);
});

// Report
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/reports', [ReportController::class, 'store']);
    Route::get('/reports', [ReportController::class, 'index']);
    Route::put('/reports/{id_report}', [ReportController::class, 'update']);
});

// Analytics (Admin only)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/analytics/dashboard', [AnalyticsController::class, 'dashboard']);
    Route::get('/analytics/users', [AnalyticsController::class, 'users']);
    Route::get('/analytics/content', [AnalyticsController::class, 'content']);
});
