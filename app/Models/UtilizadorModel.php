<?php

namespace App\Models;

use CodeIgniter\Model;

class UtilizadorModel extends Model
{
    protected $table            = 'utilizador';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'nome',
        'email',
        'telefone',
        'senha',
        'estado'
    ];

    

   

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
  

   
}
