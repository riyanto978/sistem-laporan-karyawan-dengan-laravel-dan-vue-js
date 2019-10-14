<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Memo extends Model
{
    protected $primaryKey = 'id_laporan';
    protected $table = 'memo';

    public function user()
    {
        return $this->belongsTo('App\User', 'operator', 'id');
    }

    public function pol()
    {
        return $this->belongsTo('App\models\Pol', 'id_pol', 'id_pol');
    }
}
