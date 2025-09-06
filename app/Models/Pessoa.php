<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pessoa extends Model
{
    use HasFactory;
    protected $table =  'pessoa';
    protected $primaryKey = 'id_pessoa';
    // protected $timestamps = false;

    protected $fillable = ['apelido', 'nome', 'nascimento', 'stack'];
}
