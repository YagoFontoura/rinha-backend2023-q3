<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/pessoas/{id}', [App\Http\Controllers\pessoaController::class, 'pegarPessoa']);
Route::get('/contagem-pessoas', [App\Http\Controllers\pessoaController::class, 'contagem']);
Route::get('/pessoas', [App\Http\Controllers\pessoaController::class, 'buscaPorTermos']);
Route::post('/pessoas', [App\Http\Controllers\pessoaController::class, 'salvarPessoa']);
