<?php

namespace App\Model;

use Database\Connection;
use Carbon\Carbon;

class People
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Connection::getConnection();
    }
    private function response($success, $status, $data = '', $field = '', $message = ''): array
    {
        return [
            'success' => $success,
            'status' => $status,
            'data' => $data,
            'error' => [
                'field' => $field,
                'message' => $message
            ]
        ];
    }
    public function getPeople($id): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM pessoa WHERE id = ?");
        $stmt->execute([$id]);
        $pessoa = $stmt->fetch();

        if (empty($pessoa)) {
            return $this->response(success: false, status: 404, data: ['message' => 'registry not found']);
        }

        return $this->response(success: true, status: 200, data: $pessoa);
    }
    public function verifyExistPeopleNickname($nickname)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM pessoa WHERE apelido = ?");
        $stmt->execute([$nickname]);

        if ($stmt->fetchColumn() > 0) {
            return true;
        }
        return false;
    }
    public function searchPeopleTerm($term)
    {
        $stmt = $this->pdo->prepare("
                SELECT * FROM pessoa
                WHERE id LIKE :term1 OR nome LIKE :term2 OR apelido LIKE :term3 OR stack LIKE :term4
                LIMIT 50
            ");
        $stmt->execute([
            ':term1' => "%{$term}%",
            ':term2' => "%{$term}%",
            ':term3' => "%{$term}%",
            ':term4' => "%{$term}%"
        ]);
        $term_result = $stmt->fetchAll();
        if (empty($term_result)) {
            return $this->response(false, 422, data: ['message' => 'People not found.']);
        }
        return $this->response(false, 200, data: $term_result);
    }
    public function save($id, $nickname, $name, $birth, $stack)
    {
        // ✅ Validação: campo com mais de 32 caracteres
        if (strlen($nickname) > 32) {
            $this->response(false, 422, field: 'Apelido', message: 'O Apelido não pode passar de 32 caracteres.');
        }

        // ✅ Validação: campo com mais de 100 caracteres
        if (strlen($name) > 100) {
            $this->response(false, 422, field: 'name', message: 'O nome não pode passar de 100 caracteres.');
        }

        // ✅ Validação: nome não pode ser numérico
        if (filter_var($name, FILTER_VALIDATE_INT) !== false) {
            $this->response(false, 400, '', 'name', 'O campo name não pode conter valores numéricos.');
        }

        // ✅ Validação: apelido não pode ser numérico
        if (filter_var($nickname, FILTER_VALIDATE_INT) !== false) {
            $this->response(false, 400, '', 'nickname', 'Apelido não pode ser numérico');
        }

        // ✅ Validação: data de nascimento
        if (!Carbon::hasFormat($birth, 'Y-m-d')) {
            $this->response(false, 422, '', 'Birth', 'Data de nascimento inválida');
        }

        // ✅ Validação: stack não pode conter números
        $stackArray = $data['stack'] ?? null;
        if (is_array($stackArray)) {
            foreach ($stackArray as $item) {
                // Verifica se é número ou string numérica
                if (strlen($item) > 32) {
                    $this->response(false, 422, '', 'name', 'Uma das stack excedeu o numero de 32 caracteres');
                }
                if (is_numeric($item)) {
                    $this->response(false, 422, '', 'stack', 'Stack não pode conter valores numéricos.');
                }
            }
        }

        $stack = null;
        if (is_array($stackArray) && !empty($stackArray)) {
            $stack = implode(',', $stackArray);
        }

        $stmt = $this->pdo->prepare("
                INSERT INTO pessoa (id,apelido, nome, nascimento, stack)
                VALUES (? , ?, ?, ?, ?)
            ");

        $result = $stmt->execute([
            $id,
            $nickname,
            $name,
            $birth,
            $stack
        ]);
        if (!$result) {
            return $this->response(false, 422);
        }
        return $this->response(true, 200);
    }
    public function count(): int
    {
        $amount = $this->pdo->query("SELECT COUNT(*) FROM pessoa")->fetchColumn();
        return $amount;
    }
}
