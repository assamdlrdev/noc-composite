<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'NOC API',
    description: 'ILRMS/NOC land sale workflow API - ADC, DC, CO, LRA, ASST, JS modules.'
)]
#[OA\Server(
    url: L5_SWAGGER_CONST_HOST,
    description: 'NOC API server'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'JWT access token.'
)]
class OpenApiSpec
{
}