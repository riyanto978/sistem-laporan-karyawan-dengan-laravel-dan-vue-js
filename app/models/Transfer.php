<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $primaryKey = 'id_transfer';
    protected $table = 'transfer';

    public function pol()
    {
        return $this->belongsTo('App\models\Pol', 'id_pol', 'id_pol');
    }
}
