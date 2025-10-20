<?php
require __DIR__ . '/vendor/autoload.php';

use OpenSwoole\Http\Server;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;
use App\Controllers\PessoaController;
use App\Controllers\DocumentationController;



$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$dispatcher = simpleDispatcher(function (RouteCollector $r) {

    $r->addRoute('GET', '/', [PessoaController::class, 'index']);
    $r->addRoute('GET', '/pessoas/{id:.+}', [PessoaController::class, 'pegarPessoa']);
    $r->addRoute('GET', '/pessoas', [PessoaController::class, 'buscaPorTermos']);
    $r->addRoute('POST', '/pessoas', [PessoaController::class, 'salvarPessoa']);
    $r->addRoute('GET', '/contagem-pessoas', [PessoaController::class, 'count']);
});

$server = new Server("0.0.0.0", 9501);

$server->on("start", function () {
    echo "✅ OpenSwoole rodando em http://localhost:9501\n";
});

$server->on("request", function ($request, $response) use ($dispatcher) {
    // 🔹 CORS global
    $response->header("Access-Control-Allow-Origin", "*");
    $response->header("Access-Control-Allow-Methods", "GET, POST, PUT, DELETE, OPTIONS");
    $response->header("Access-Control-Allow-Headers", "Content-Type, Authorization, X-Requested-With");

    // 🔹 Tratamento de preflight
    if ($request->server['request_method'] === 'OPTIONS') {
        $response->status(204);
        $response->end();
        return;
    }

    // 🔹 Continua com o dispatcher normalmente
    $routeInfo = $dispatcher->dispatch(
        $request->server['request_method'],
        $request->server['request_uri']
    );

    switch ($routeInfo[0]) {
        case FastRoute\Dispatcher::NOT_FOUND:
            $response->status(404);
            $response->end(json_encode(['error' => 'Not Found']));
            break;
        case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
            $response->status(405);
            $response->end(json_encode(['error' => 'Method Not Allowed']));
            break;
        case FastRoute\Dispatcher::FOUND:
            [$class, $method] = $routeInfo[1];
            (new $class())->$method($request, $response, $routeInfo[2]);
            break;
    }
});

$server->start();
