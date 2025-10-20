<?php

namespace App\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "API Peoples - Rinha Backend2023 - Q3",
    version: "1.1",
    description: "API para gerenciamento de pessoas - Rinha de Backend 2023 Q3"
)]
#[OA\Server(
    url: "http://localhost:9999",
    description: "Servidor Local"
)]
class OpenApiSpec
{
}
