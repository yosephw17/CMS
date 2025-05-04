<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstructorTimeSlot extends Model
{
    use HasFactory;
    protected $table = 'instructor_time_slot';
    protected $fillable = [
        'instructor_id',
        'time_slot_id',
        'reason' 
    ];

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }
    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class);
    }
    public function isUnavailable(): bool
    {
        // You can add custom logic here if needed
        return true;
    }
}
