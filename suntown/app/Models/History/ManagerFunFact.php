<?php

namespace App\Models\History;

use Illuminate\Database\Eloquent\Model;

class ManagerFunFact extends Model
{
    protected $connection = 'ffb';

    protected $guarded = [];

    protected $table = 'manager_fun_facts';

    public function manager()
    {
        return $this->belongsTo(Manager::class);
    }
}
