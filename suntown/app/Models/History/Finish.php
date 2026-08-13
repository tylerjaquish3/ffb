<?php

namespace App\Models\History;

use Illuminate\Database\Eloquent\Model;

class Finish extends Model
{
    protected $connection = 'ffb';

    protected $guarded = [];

    protected $table = 'finishes';

    public $timestamps = false;
}
