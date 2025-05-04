<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleResult extends Model
{
    use HasFactory;
    protected $fillable=[

     'instructor_id','course_id','section_id','schedule_id','room_id'
      ];
         public function course()
         {
             return $this->belongsTo(Course::class);
         }
     
         public function instructor()
         {
             return $this->belongsTo(Instructor::class);
         }
     
         public function section()
         {
             return $this->belongsTo(Section::class);
         }
         public function room()
        {
           return $this->belongsTo(Room::class);
        }
      public function schedule()
        {
            return $this->belongsTo(Schedule::class);
        }
        public function timeSlots()
        {
            return $this->belongsToMany(TimeSlot::class, 'schedule_time_slot');
        }
        public function scheduleTimeSlots()
{
    return $this->hasMany(ScheduleTimeSlot::class, 'schedule_result_id', 'id');
}

}
    

