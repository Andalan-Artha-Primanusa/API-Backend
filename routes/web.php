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

// Fix for storage link on shared hosting (Hostinger)
// This serves files directly from storage/app/public because symlinks are often disabled
Route::get('/storage/{path}', function ($path) {
    $path = str_replace('..', '', $path);
    $fullPath = storage_path('app/public/' . $path);

    if (!\Illuminate\Support\Facades\File::exists($fullPath)) {
        abort(404);
    }

    $file = \Illuminate\Support\Facades\File::get($fullPath);
    $type = \Illuminate\Support\Facades\File::mimeType($fullPath);

    return response($file)->header("Content-Type", $type);
})->where('path', '.*');
