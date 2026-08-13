<?php

namespace App\Models\History;

use Illuminate\Database\Eloquent\Model;

class NflTeam extends Model
{
    protected $connection = 'ffb';

    protected $table = 'nfl_teams';

    protected $guarded = [];

    public $timestamps = false;
}
