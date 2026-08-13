<?php

namespace App\Models\History;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $connection = 'ffb';

    protected $guarded = [];

    protected $table = 'schedule';

    public $timestamps = false;
}
