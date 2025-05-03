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

    public function toggleBreak()
    {
        $this->update(['is_break' => !$this->is_break]);
        return $this->is_break;
    }

    /**
     * Scope for break time slots
     */
    public function scopeBreaks($query)
    {
        return $query->where('is_break', true);
    }

    /**
     * Scope for non-break time slots
     */
    public function scopeNotBreaks($query)
    {
        return $query->where('is_break', false);
    }
}