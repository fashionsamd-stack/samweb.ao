<?php

namespace App\Models;

use CodeIgniter\Model;

class ItemPedidoModel extends Model
{
    protected $table            = 'itempedidos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $allowedFields    = [
        'pedido_id',
        'produto_id',
        'quantidade',
        'preco',
        'subtotal'
    ];

    

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    


}
