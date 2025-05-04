<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YearSemesterCourse extends Model
{
    use HasFactory;
    protected $fillable = ['year_id', 'semester_id', 'course_id','department_id'];

    public function year()
    {
        return $this->belongsTo(Year::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    protected $table = 'year_semester_courses';  // Update to the new name
    
    public function lectureRoomPreference()
    {
        return $this->belongsTo(Room::class, 'preferred_lecture_room_id');
    }

    public function labRoomPreference()
    {
        return $this->belongsTo(Room::class, 'preferred_lab_room_id');
    }
}
