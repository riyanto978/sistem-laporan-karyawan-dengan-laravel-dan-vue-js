<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Bongkaran extends Model
{
    protected $primaryKey = 'id_bongkaran';
    protected $table = 'bongkaran';

    public function user()
    {
        return $this->belongsTo('App\User','operator','id');
    }
    
}
