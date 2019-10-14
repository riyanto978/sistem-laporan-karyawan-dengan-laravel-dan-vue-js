<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class input_bongkaran extends Model
{
    protected $primaryKey = 'id_input';
    protected $table = 'input_bongkaran';

    public function user()
    {
        return $this->belongsTo('App\User','operator','id');
    }
    
}
