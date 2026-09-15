<?php

namespace App\Models;

use CodeIgniter\Model;

class ProvisionamentoModel extends Model
{
    protected $table = 'provisionamento';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';

    protected $allowedFields = [
        'pedido_id',
        'item_pedido_id',
        'tipo_servico',
        'estado',
        'mensagem',
        'data_inicio',
        'data_conclusao',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
