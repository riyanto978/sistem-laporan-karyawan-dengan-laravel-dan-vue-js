<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Applet extends Model
{
    protected $primaryKey = 'id_applet';
    protected $table = 'applet';

    public function user()
    {
        return $this->belongsTo('App\User','operator','id');
    }

    public function periode()
    {
        return $this->belongsTo('App\models\Periode', 'id_periode', 'id_periode');
    }

    public function pol()
    {
        return $this->belongsTo('App\models\Pol', 'id_pol', 'id_pol');
    }
}
