<?php

namespace App\Models;

use CodeIgniter\Model;

class DominioModel extends Model
{
    protected $table            = 'dominios';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
  
    protected $allowedFields    = [
        'cliente_id',
        'nome',
        'inicio',
        'expiracao',
        'renovacao_auto',
        'estado'
    ];

    

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    


}
