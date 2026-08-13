<?php

namespace App\Models\History;

use Illuminate\Database\Eloquent\Model;

class Draft extends Model
{
    protected $connection = 'ffb';

    protected $guarded = [];

    protected $table = 'draft';

    public $timestamps = false;
}
