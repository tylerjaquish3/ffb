<?php

namespace App\Models\History;

use Illuminate\Database\Eloquent\Model;

class RecordLog extends Model
{
    protected $connection = 'ffb';

    protected $table = 'record_log';

    protected $fillable = [
        'manager_id',
        'year',
        'week',
        'fun_fact_id',
        'value',
        'note',
        'new_leader',
    ];

    protected $casts = [
        'new_leader' => 'boolean',
    ];

    public function funFact()
    {
        return $this->belongsTo(FunFact::class, 'fun_fact_id');
    }
}
