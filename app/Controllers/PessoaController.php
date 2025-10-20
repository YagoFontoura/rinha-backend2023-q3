<?php

namespace App\Controllers;

use App\Model\People;

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

    public function index($request, $response)
    {
        $this->jsonResponse($response, 200, ['status' => 'Online']);
    }

    public function pegarPessoa($request, $response, $vars)
    {
        $id = $vars['id'] ?? null;

        if (!$id) {
            return $this->jsonResponse($response, 400, ['message' => 'id is required']);
        }

        $info = $this->people->getPeople($id);
        $this->jsonResponse($response, $info['status'], $info['data']);
    }

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

    public function count($request, $response)
    {
        $count = $this->people->count();
        $this->jsonResponse($response, 200, ['total' => (int)$count]);
    }
}
