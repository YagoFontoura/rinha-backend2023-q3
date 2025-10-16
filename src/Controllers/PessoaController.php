<?php

namespace App\Controllers;

use PDO;

class PessoaController
{
    private static ?PDO $pdo = null;

    // ⚡ Connection Pool - reutiliza conexão ao invés de criar nova a cada request
    private function db(): PDO
    {
        if (self::$pdo === null) {
            self::$pdo = new PDO(
                "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_DATABASE']};charset=utf8",
                $_ENV['DB_USERNAME'],
                $_ENV['DB_PASSWORD'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_PERSISTENT => true, // Conexão persistente
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        }
        return self::$pdo;
    }

    private function isValidDate($date): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }
        
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    // 🔧 Helper para resposta JSON
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
            return $this->jsonResponse($response, 400, ['mensagem' => 'ID inválido']);
        }

        $pdo = $this->db();
        $stmt = $pdo->prepare("SELECT * FROM pessoa WHERE id = ?");
        $stmt->execute([$id]);

        $pessoa = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pessoa) {
            return $this->jsonResponse($response, 404, ['mensagem' => 'Pessoa não encontrada']);
        }

        $this->jsonResponse($response, 200, $pessoa);
    }

    public function buscaPorTermos($request, $response)
    {
        $params = $request->get ?? [];
        $termo = $params['t'] ?? null;

        if (!$termo) {
            return $this->jsonResponse($response, 400, ['mensagem' => 'Não pode consultar sem parâmetro']);
        }

        try {
            $pdo = $this->db();
            $stmt = $pdo->prepare("
                SELECT * FROM pessoa
                WHERE id LIKE :termo1 OR nome LIKE :termo2 OR apelido LIKE :termo3 OR stack LIKE :termo4
                LIMIT 50
            ");
            $stmt->execute([':termo1' => "%{$termo}%",
                ':termo2' => "%{$termo}%",
                ':termo3' => "%{$termo}%",
                ':termo4' => "%{$termo}%"]);
            
           return  $this->jsonResponse($response, 200, $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (\PDOException $e) {
            error_log("Erro na busca: " . $e->getMessage());
            $this->jsonResponse($response, 500, ['mensagem' => $e->getMessage()]);
        }
    }

    public function salvarPessoa($request, $response)
    {
        // Parse do JSON
        $data = json_decode($request->rawContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->jsonResponse($response, 400, ['mensagem' => 'JSON inválido']);
        }

        // ✅ Validação: campos obrigatórios
        if (empty($data['nome']) || empty($data['apelido']) || empty($data['nascimento'])) {
            return $this->jsonResponse($response, 422, ['mensagem' => 'Não pode ser enviado valor null']);
        }

        // ✅ Validação: campo com mais de 32 caracteres
        if(strlen($data['apelido']) > 32) {
            return $this->jsonResponse($response,422, ['mensagem' => 'O Apelido não pode passar de 32 caracteres.']);
        }

        // ✅ Validação: campo com mais de 100 caracteres
        if(strlen($data['nome']) > 100) {
            return $this->jsonResponse($response,422, ['mensagem' => 'O nome não pode passar de 100 caracteres.']);
        }

        // ✅ Validação: nome não pode ser numérico
        if (filter_var($data['nome'], FILTER_VALIDATE_INT) !== false) {
            return $this->jsonResponse($response, 400, ['mensagem' => 'Não pode ser inserido valor numérico no nome']);
        }

        // ✅ Validação: apelido não pode ser numérico
        if (filter_var($data['apelido'], FILTER_VALIDATE_INT) !== false) {
            return $this->jsonResponse($response, 400, ['mensagem' => 'Apelido não pode ser numérico']);
        }

        // ✅ Validação: data de nascimento
        if (!$this->isValidDate($data['nascimento'])) {
            return $this->jsonResponse($response, 422, ['mensagem' => 'Data de nascimento inválida']);
        }

        // ✅ Validação: stack não pode conter números
        $stackArray = $data['stack'] ?? null;
        if (is_array($stackArray)) {
            foreach ($stackArray as $item) {
                // Verifica se é número ou string numérica
                if(strlen($item) > 32) {
                    return $this->jsonResponse($response,422,['mensagem' => 'Uma das stack excedeu o numero de 32 caracteres']);
                }
                if (is_numeric($item)) {
                    return $this->jsonResponse($response, 422, ['mensagem' => 'Stack não pode conter valores numéricos']);
                }
            }
        }

        try {
            $pdo = $this->db();

            // ✅ Verificar se apelido já existe
            $check = $pdo->prepare("SELECT COUNT(*) FROM pessoa WHERE apelido = ?");
            $check->execute([$data['apelido']]);

            if ($check->fetchColumn() > 0) {
                return $this->jsonResponse($response, 422, ['mensagem' => 'Apelido já registrado']);
            }


            // Processar stack
            $stack = null;
            if (is_array($stackArray) && !empty($stackArray)) {
                $stack = implode(',', $stackArray);
            }
            $id = uniqid();
            // ✅ Inserir no banco
            $stmt = $pdo->prepare("
                INSERT INTO pessoa (id,apelido, nome, nascimento, stack)
                VALUES (? , ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $id,
                $data['apelido'],
                $data['nome'],
                $data['nascimento'],
                $stack
            ]);

            // ✅ Retornar redirect (status 201 + Location header)
            $response->status(201);
            $response->header('Location', "/pessoas/{$id}");
            $response->header('Content-Type', 'application/json');
            $response->end(json_encode(['id' => $id]));

        } catch (\PDOException $e) {
            error_log("Erro ao salvar pessoa: " . $e->getMessage());
            $this->jsonResponse($response, 500, ['mensagem' => 'Erro ao salvar pessoa']);
        }
    }

    public function contagem($request, $response)
    {
        try {
            $pdo = $this->db();
            $count = $pdo->query("SELECT COUNT(*) FROM pessoa")->fetchColumn();
            
            $this->jsonResponse($response, 200, ['total' => (int)$count]);
        } catch (\PDOException $e) {
            error_log("Erro na contagem: " . $e->getMessage());
            $this->jsonResponse($response, 500, ['mensagem' => 'Erro ao contar']);
        }
    }
}