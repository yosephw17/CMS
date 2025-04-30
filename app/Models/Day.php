<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Day extends Model
{
    protected $fillable = ['name'];

    public function timeSlots()
    {
        return $this->hasMany(TimeSlot::class, 'day_id');
    }
}