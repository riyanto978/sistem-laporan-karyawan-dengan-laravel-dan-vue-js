<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Pol extends Model
{
	protected $primaryKey = 'id_pol';
	protected $table='pol';

	public function applets()
	{
		return $this->hasMany('App\models\Applet', 'id_pol', 'id_pol');
	}

	public function periode()
	{
		return $this->belongsTo('App\models\Periode','id_periode','id_periode');
	}
}
