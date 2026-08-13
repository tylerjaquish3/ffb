<?php

namespace App\Models\History;

use Illuminate\Database\Eloquent\Model;

class SeasonPosition extends Model
{
    protected $connection = 'ffb';

    protected $guarded = [];

    protected $table = 'season_positions';

    public $timestamps = false;
}
