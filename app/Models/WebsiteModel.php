<?php

namespace App\Models;

use CodeIgniter\Model;

class WebsiteModel extends Model
{
    protected $table            = 'websites';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $allowedFields    = [
        'cliente_id',
        'produto_id',
        'site',
        'inicio',
        'prazo',
        'estado'
    ];

   
    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
   


}
