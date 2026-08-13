<?php

namespace App\Models\History;

use Illuminate\Database\Eloquent\Model;

class RegularSeasonMatchup extends Model
{
    protected $connection = 'ffb';

    protected $guarded = [];

    protected $table = 'regular_season_matchups';

    public $timestamps = false;
}
