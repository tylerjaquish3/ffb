<?php

namespace App\Models\History;

use Illuminate\Database\Eloquent\Model;

class FunFact extends Model
{
    protected $connection = 'ffb';

    protected $guarded = [];

    protected $table = 'fun_facts';

    public $timestamps = false;
}
