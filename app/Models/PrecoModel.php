<?php

namespace App\Models;

use CodeIgniter\Model;

class PrecoModel extends Model
{
    protected $table            = 'preco';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
   
    protected $allowedFields    = [
        'produto_id',
        'periodo',
        'valor'
    ];

 

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
   


}
