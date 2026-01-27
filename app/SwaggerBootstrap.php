<?php

namespace App;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Laravel 11 API",
    version: "1.0.0",
    description: "API Documentation Laravel 11"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT"
)]

/* =====================
 | AUTH
 ===================== */
#[OA\PathItem(
    path: "/api/login",
    post: new OA\Post(
        operationId: "auth_login",
        tags: ["Auth"],
        summary: "Login user",
        responses: [
            new OA\Response(response: 200, description: "OK"),
            new OA\Response(response: 401, description: "Unauthorized")
        ]
    )
)]
#[OA\PathItem(
    path: "/api/register",
    post: new OA\Post(
        operationId: "auth_register",
        tags: ["Auth"],
        summary: "Register user",
        responses: [
            new OA\Response(response: 201, description: "Created"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )
)]
#[OA\PathItem(
    path: "/api/logout",
    post: new OA\Post(
        operationId: "auth_logout",
        tags: ["Auth"],
        security: [["bearerAuth" => []]],
        summary: "Logout user",
        responses: [
            new OA\Response(response: 200, description: "OK"),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )
)]

/* =====================
 | USER PROFILE
 ===================== */
#[OA\PathItem(
    path: "/api/users",
    get: new OA\Get(
        operationId: "user_get_all",
        tags: ["User Profile"],
        security: [["bearerAuth" => []]],
        summary: "Get all users",
        responses: [
            new OA\Response(response: 200, description: "OK")
        ]
    ),
    post: new OA\Post(
        operationId: "user_create",
        tags: ["User Profile"],
        security: [["bearerAuth" => []]],
        summary: "Create user",
        responses: [
            new OA\Response(response: 201, description: "Created")
        ]
    )
)]
#[OA\PathItem(
    path: "/api/users/{id}",
    get: new OA\Get(
        operationId: "user_get_by_id",
        tags: ["User Profile"],
        security: [["bearerAuth" => []]],
        summary: "Get user by ID",
        responses: [
            new OA\Response(response: 200, description: "OK"),
            new OA\Response(response: 404, description: "Not found")
        ]
    ),
    put: new OA\Put(
        operationId: "user_update",
        tags: ["User Profile"],
        security: [["bearerAuth" => []]],
        summary: "Update user",
        responses: [
            new OA\Response(response: 200, description: "Updated")
        ]
    ),
    delete: new OA\Delete(
        operationId: "user_delete",
        tags: ["User Profile"],
        security: [["bearerAuth" => []]],
        summary: "Delete user",
        responses: [
            new OA\Response(response: 200, description: "Deleted")
        ]
    )
)]

/* =====================
 | SYSTEM
 ===================== */
#[OA\PathItem(
    path: "/api/_health",
    get: new OA\Get(
        operationId: "system_health",
        tags: ["System"],
        summary: "Health check",
        responses: [
            new OA\Response(response: 200, description: "OK")
        ]
    )
)]
class SwaggerBootstrap {}
