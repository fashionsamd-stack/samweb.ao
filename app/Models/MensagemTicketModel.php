<?php

namespace App\Models;

use CodeIgniter\Model;

class MensagemTicketModel extends Model
{
    protected $table            = 'mensagemtickets';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $allowedFields    = [
        'ticket_id',
        'utilizador_id',
        'mensagem'
    ];

    

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    


}
