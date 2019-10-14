<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Preperso extends Model
{
    protected $primaryKey = 'id_preperso';
    protected $table = 'preperso';

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

    public function applet()
    {
        return $this->belongsTo('App\models\Applet', 'old', 'id_applet');
    }
}
