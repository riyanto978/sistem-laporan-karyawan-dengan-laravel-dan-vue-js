<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Periode extends Model
{
    protected $primaryKey = 'id_periode';
    protected $table = 'periode';

    public function Applets()
    {
        return $this->hasMany('App\models\Applet', 'id_periode', 'id_periode');
    }
}
