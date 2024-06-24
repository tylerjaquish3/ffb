<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NflTeam extends Model
{
    use HasFactory;

    protected $table = 'nfl_teams';

    protected $guarded = [];

    public $timestamps = false;

}
