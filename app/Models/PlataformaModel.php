<?php

namespace App\Models;

use CodeIgniter\Model;

class PlataformaModel extends Model
{
    protected $table            = 'plataformas';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
  
    protected $allowedFields    = [
        'empresa_id',
        'nome',
        'dominio',
        'moeda',
        'estado'
    ];


    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    


}
