<?php

namespace App\Models\History;

use Illuminate\Database\Eloquent\Model;

class PlayoffRoster extends Model
{
    protected $connection = 'ffb';

    protected $guarded = [];

    protected $table = 'playoff_rosters';

    public $timestamps = false;
}
