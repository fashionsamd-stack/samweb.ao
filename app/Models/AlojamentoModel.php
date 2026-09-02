<?php

namespace App\Models;

use CodeIgniter\Model;

class AlojamentoModel extends Model
{
    protected $table            = 'alojamento';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
  
    protected $allowedFields    = [
        'cliente_id',
        'produto_id',
        'dominio_id',
        'inicio',
        'expiracao',
        'estado'
    ];


    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
   


}
