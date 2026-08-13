<?php

namespace App\Models\History;

use Illuminate\Database\Eloquent\Model;

class PlayoffMatchup extends Model
{
    protected $connection = 'ffb';

    protected $guarded = [];

    protected $table = 'playoff_matchups';

    public $timestamps = false;
}
