<?php

namespace App\Models\History;

use Illuminate\Database\Eloquent\Model;

class TeamName extends Model
{
    protected $connection = 'ffb';

    protected $fillable = [
        'manager_id',
        'year',
        'name',
        'moves',
        'trades',
    ];

    protected $table = 'team_names';

    public $timestamps = false;

    public function manager()
    {
        return $this->belongsTo(Manager::class);
    }
}
