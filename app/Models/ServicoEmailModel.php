<?php

namespace App\Models;

use CodeIgniter\Model;

class ServicoEmailModel extends Model
{
    protected $table            = 'servico_email';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
  
    protected $allowedFields    = [
        'cliente_id',
        'dominio_id',
        'produto_id',
        'inicio',
        'expiracao',
        'estado'
    ];

  

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

 

   
}
