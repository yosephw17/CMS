<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{

    use HasFactory;
    protected $fillable = ['day_id', 'start_time', 'end_time', 'is_break'];


    public function day()
    {
        return $this->belongsTo(Day::class);
    }
    public function unavailableInstructors(): BelongsToMany
    {
        return $this->belongsToMany(Instructor::class, 'instructor_time_slot')
            ->using(InstructorTimeSlot::class)
            ->withTimestamps();
    }
    public function scheduleResults(): BelongsToMany
    {
        return $this->belongsToMany(ScheduleResult::class, 'schedule_time_slot')
            ->withTimestamps();
    }
    public function scheduleTimeSlots()
    {
        return $this->hasMany(ScheduleTimeSlot::class, 'time_slot_id');
    }

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

