<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pessoa;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;

class pessoaController extends Controller
{
    public function index()
    {
        return 'Online';
    }
    public function pegarPessoa($id)
    {
        return Pessoa::find($id);
    }
    public function buscaPorTermos(Request $request)
    {
        $termo = $request['t'];
        if (!isset($termo)) {
            return response()->json(["mensagem" => "Não pode consultar sem parametro."], status: 400);
        }
        return Pessoa::where('nome', 'like', "%{$termo}%")->orWhere('apelido', 'like', "%{$termo}%")->orWhere('stack', 'like', "%{$termo}%")->limit(50)->get();
    }
    public function salvarPessoa(Request $request)
    {
        if (Pessoa::where(column: 'apelido', operator: $request->apelido)->exists()) {
            return response()->json(data: ["mensagem" => "Apelido já registrado"], status: 422);
        }

        if ($request['nome'] == null or $request['apelido'] == null) {
            return response()->json(data: ["mensagem" => "Não pode ser enviado valor null"], status: 422);
        }

        if (filter_var($request['nome'], FILTER_VALIDATE_INT)) {
            return response()->json(data: ["mensagem" => "Não pode ser inserido valor numérico"], status: 400);
        }

        if (collect($request['stack'])->contains(fn($item) => is_int($item) || is_numeric($item))) {
            return response()->json(data: ["mensagem" => "Não pode existir valor numérico no array"], status: 400);
        }

        $stack = is_array($request['stack']) ? implode(',', $request['stack']) : null;

        $resultado = Pessoa::create([
            'apelido' => $request['apelido'],
            'nome' => $request['nome'],
            'nascimento' => $request['nascimento'],
            'stack' => $stack
        ]);

        return redirect('/api/pessoas/' . $resultado->id_pessoa);
    }
    public function contagem(): int
    {
        $contagem = Pessoa::count();
        return $contagem;
    }
}
