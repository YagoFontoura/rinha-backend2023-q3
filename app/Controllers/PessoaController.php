<?php

namespace App\Controllers;

use App\Model\People;
use OpenApi\Attributes as OA;

class pessoaController
{
    private $people;
    public function __construct()
    {
        $this->people = new People();
    }

    private function jsonResponse($response, int $status, array $data): void
    {
        $response->status($status);
        $response->header('Content-Type', 'application/json');
        $response->end(json_encode($data));
    }
    #[OA\Get(
        path: "/",
        operationId: "getStatus",
        tags: ["Status"],
        responses: [
            new OA\Response(
                response: "200",
                description: "Return service status",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "Online")
                    ]
                )
            )
        ]
    )]
    public function index($request, $response)
    {
        $this->jsonResponse($response, 200, ['status' => 'Online']);
    }

    #[OA\Get(
        path: "/pessoas/{id}",
        operationId: "getPessoa",
        tags: ["Pessoas"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID da pessoa",
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(response: "200", description: "Pessoa encontrada"),
            new OA\Response(response: "400", description: "ID não fornecido"),
            new OA\Response(response: "404", description: "Pessoa não encontrada")
        ]
    )]
    public function pegarPessoa($request, $response, $vars)
    {
        $id = $vars['id'] ?? null;

        if (!$id) {
            return $this->jsonResponse($response, 400, ['message' => 'id is required']);
        }

        $info = $this->people->getPeople($id);
        $this->jsonResponse($response, $info['status'], $info['data']);
    }

    #[OA\Get(
        path: "/pessoas",
        operationId: "buscaPorTermos",
        tags: ["Pessoas"],
        parameters: [
            new OA\Parameter(
                name: "t",
                in: "query",
                required: true,
                description: "Termo de busca",
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(response: "200", description: "Lista de pessoas encontradas"),
            new OA\Response(response: "400", description: "Parâmetro de busca não fornecido")
        ]
    )]
    public function buscaPorTermos($request, $response)
    {
        $params = $request->get ?? [];
        $term = $params['t'] ?? null;

        if (!$term) {
            return $this->jsonResponse($response, 400, ['mensagem' => 'Não pode consultar sem parâmetro']);
        }

        $result_term = $this->people->searchPeopleTerm($term);

        return  $this->jsonResponse($response, $result_term['status'], $result_term['data']);
    }

    #[OA\Post(
        path: "/pessoas",
        operationId: "salvarPessoa",
        tags: ["Pessoas"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["apelido", "nome", "nascimento"],
                properties: [
                    new OA\Property(property: "apelido", type: "string", example: "ze"),
                    new OA\Property(property: "nome", type: "string", example: "José Silva"),
                    new OA\Property(property: "nascimento", type: "string", format: "date", example: "1990-01-01"),
                    new OA\Property(
                        property: "stack",
                        type: "array",
                        items: new OA\Items(type: "string"),
                        example: ["PHP", "Python", "JavaScript"]
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: "201",
                description: "Pessoa criada com sucesso",
                headers: [
                    new OA\Header(
                        header: "Location",
                        description: "URI da pessoa criada",
                        schema: new OA\Schema(type: "string")
                    )
                ]
            ),
            new OA\Response(response: "400", description: "JSON inválido"),
            new OA\Response(response: "422", description: "Dados inválidos ou apelido já registrado")
        ]
    )]
    public function salvarPessoa($request, $response)
    {
        $data = json_decode($request->rawContent(), associative: true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->jsonResponse($response, 400, ['mensagem' => 'JSON inválido']);
        }

        // Validate required input
        if (empty($data['nome']) || empty($data['apelido']) || empty($data['nascimento'])) {
            return $this->jsonResponse($response, 422, ['mensagem' => 'Não pode ser enviado valor null']);
        }

        $result_exist = $this->people->verifyExistPeopleNickname($data['apelido']);

        if ($result_exist) {
            $this->jsonResponse($response, 422, ['mensagem' => 'Apelido já registrado']);
        }

        $id = uniqid();

        $result_save = $this->people->save($id, $data['apelido'], $data['nome'], $data['nascimento'], $data['stack']);
        if (!$result_save['success']) {
            $this->jsonResponse($response, $result_save['status'], ['message' => $result_save['error']['message']]);
        }
        $response->status(201);
        $response->header('Location', "/pessoas/{$id}");
        $response->header('Content-Type', 'application/json');
        $response->end(json_encode(['id' => $id]));
    }

    #[OA\Get(
        path: "/contagem-pessoas",
        operationId: "count",
        tags: ["Pessoas"],
        responses: [
            new OA\Response(
                response: "200",
                description: "Total de pessoas cadastradas",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "total", type: "integer", example: 100)
                    ]
                )
            )
        ]
    )]
    public function count($request, $response)
    {
        $count = $this->people->count();
        $this->jsonResponse($response, 200, ['total' => (int)$count]);
    }
}
