<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManagerFunFact extends Model
{

    protected $guarded = [];

    protected $table = 'manager_fun_facts';

    public function manager()
    {
        return $this->belongsTo('App\Models\Manager');
    }
}
