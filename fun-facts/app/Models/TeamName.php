<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamName extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'manager_id',
        'year',
        'name',
        'moves',
        'trades'
    ];

    protected $table = 'team_names';

    public $timestamps = false;

    public function manager()
    {
        return $this->belongsTo('App\Models\Manager');
    }
}
