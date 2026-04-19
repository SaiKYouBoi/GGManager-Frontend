<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="E-Sport Tournament API",
 *     version="1.0.0",
 *     description="API for managing e-sport tournaments, registrations, brackets and match scores.",
 *     @OA\Contact(email="admin@esport.com")
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8080/api",
 *     description="Local server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class SwaggerController {}
