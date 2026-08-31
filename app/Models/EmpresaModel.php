<?php

namespace App\Models;

use CodeIgniter\Model;

class EmpresaModel extends Model
{
    protected $table            = 'empresas';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
 
    protected $allowedFields    = [
        'nome',
        'nif',
        'email',
        'telefone',
        'estado'
    ];

   


    // Dates
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    

  
}
