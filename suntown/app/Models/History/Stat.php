<?php

namespace App\Models\History;

use Illuminate\Database\Eloquent\Model;

class Stat extends Model
{
    protected $connection = 'ffb';

    protected $guarded = [];

    protected $table = 'stats';

    public $timestamps = false;

    public function roster()
    {
        return $this->hasOne(Roster::class);
    }
}
