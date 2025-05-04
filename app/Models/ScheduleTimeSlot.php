<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleTimeSlot extends Model
{
    use HasFactory;
    protected $table = 'schedule_time_slot';
    protected $fillable = [
        'schedule_result_id',
        'time_slot_id',
        'created_by',
        'updated_by'
    ];
    public function scheduleResult()
    {
        return $this->belongsTo(ScheduleResult::class, 'schedule_result_id');
    }
    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class);
    }
}
