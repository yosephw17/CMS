<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstructorTimeSlot extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'instructor_time_slot';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'instructor_id',
        'time_slot_id',
    ];

    /**
     * Get the instructor associated with this time slot assignment.
     */
    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }

    /**
     * Get the time slot associated with this assignment.
     */
    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class);
    }
}