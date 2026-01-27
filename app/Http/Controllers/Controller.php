<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 * @OA\Info(
 *     title="Laravel 11 API",
 *     version="1.0.0",
 *     description="API Documentation Laravel 11 + Sanctum"
 * )
 */
class Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
