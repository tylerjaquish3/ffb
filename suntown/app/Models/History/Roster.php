<?php

namespace App\Models\History;

use Illuminate\Database\Eloquent\Model;

class Roster extends Model
{
    protected $connection = 'ffb';

    protected $guarded = [];

    protected $table = 'rosters';

    public $timestamps = false;

    public function stat()
    {
        return $this->hasOne(Stat::class);
    }
}
