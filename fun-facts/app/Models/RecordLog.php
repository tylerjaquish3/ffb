<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecordLog extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'record_log';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'manager_id',
        'year',
        'week',
        'fun_fact_id',
        'value',
        'note',
        'new_leader',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'new_leader' => 'boolean',
    ];

    /**
     * Get the fun fact associated with this record.
     */
    public function funFact()
    {
        return $this->belongsTo(FunFact::class, 'fun_fact_id');
    }
}
