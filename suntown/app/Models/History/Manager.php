<?php

namespace App\Models\History;

use Illuminate\Database\Eloquent\Model;

class Manager extends Model
{
    protected $connection = 'ffb';

    protected $fillable = [
        'name',
        'yahoo_id',
    ];

    protected $table = 'managers';

    public $timestamps = false;
}
