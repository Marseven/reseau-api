<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'ReseauApp API - Eramet Comilog',
    description: "API REST de gestion d'inventaire et d'infrastructure réseau pour Eramet Comilog. Authentification via Laravel Sanctum (Bearer Token).",
    contact: new OA\Contact(
        name: 'JOBS-Conseil',
        email: 'contact@jobs-conseil.tech'
    )
)]
#[OA\Server(
    url: '/api/v1',
    description: 'API v1'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Sanctum Token',
    description: 'Entrez votre token Sanctum obtenu via POST /api/v1/auth/login'
)]
abstract class Controller
{
    //
}
