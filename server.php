<?php
require __DIR__ . '/vendor/autoload.php';

use OpenSwoole\Http\Server;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;
use App\Controllers\PessoaController;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

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

$server->on("request", function ($req, $res) use ($dispatcher) {
    $res->header("Content-Type", "application/json");

    $uri = $req->server['request_uri'];
    $method = $req->server['request_method'];

    $routeInfo = $dispatcher->dispatch($method, $uri);

    switch ($routeInfo[0]) {
        case \FastRoute\Dispatcher::NOT_FOUND:
            $res->status(404);
            $res->end(json_encode(["mensagem" => "Rota não encontrada."]));
            break;

        case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
            $res->status(405);
            $res->end(json_encode(["mensagem" => "Método não permitido."]));
            break;

        case \FastRoute\Dispatcher::FOUND:
            [$class, $method] = $routeInfo[1];
            $vars = $routeInfo[2];
            $controller = new $class();
            $response = $controller->$method($req, $res, $vars);
            $res->end(json_encode($response));
            break;
    }
});

$server->start();
