<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class transfer_bongkaran extends Model
{
    protected $primaryKey = 'id_bongkaran';
    protected $table = 'bongkaran';

    const CREATED_AT = 'waktu_transfer';
    const UPDATED_AT =  'waktu_transfer';

    public function user()
    {
        return $this->belongsTo('App\User', 'operator', 'id');
    }
}
