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
Route::get('/storage/{path}', function ($path) {
    $path = str_replace('..', '', $path);
    if (!Storage::disk('public')->exists($path)) {
        abort(404);
    }
    
    $file = Storage::disk('public')->get($path);
    $type = Storage::disk('public')->mimeType($path);
    
    return Response::make($file, 200)->header("Content-Type", $type);
})->where('path', '.*');
