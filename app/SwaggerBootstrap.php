<?php

namespace App;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Laravel 11 API",
    version: "1.0.0",
    description: "API Documentation Laravel 11"
)]
#[OA\PathItem(
    path: "/api/__health"
)]
class SwaggerBootstrap {}
