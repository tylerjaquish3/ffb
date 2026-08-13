<?php

namespace App\Models\History;

use Illuminate\Database\Eloquent\Model;

class SeasonManager extends Model
{
    protected $connection = 'ffb';

    protected $guarded = [];

    protected $table = 'season_managers';

    public $timestamps = false;
}
