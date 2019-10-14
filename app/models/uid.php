<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;


class uid extends Model
{
	protected $primaryKey = 'id';
    protected $table='uid';

    public $timestamps = false;
}
