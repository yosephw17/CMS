<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    protected $fillable = ['day_id', 'start_time', 'end_time', 'is_break'];

    public function day()
    {
        return $this->belongsTo(Day::class, 'day_id');
    }
}