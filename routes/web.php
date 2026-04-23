<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;


Route::get('/', function () {
    return response()->json([
        'success' => true,
        'message' => 'API HRIS aktif 🚀'
    ]);
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');


/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/

Route::get('/employee/profile', [ProfileController::class, 'index'])
    ->name('employee.profile');

Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

// Fallback for storage link on shared hosting (Hostinger)
// This replaces 'php artisan storage:link' which is often disabled on shared hosting
Route::get('/storage/{path}', function ($path) {
    $path = str_replace('..', '', $path);
    
    // Construct the absolute path manually to avoid Storage facade issues on restricted servers
    $fullPath = storage_path('app/public/' . $path);
    
    if (!file_exists($fullPath) || is_dir($fullPath)) {
        abort(404);
    }
    
    return response()->file($fullPath);
})->where('path', '.*');
