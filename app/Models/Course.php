<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;
   

    protected $fillable = [
        'name',
        'course_code',
        'cp',
        'lecture_cp',
        'lab_cp',
        'department_id'
    ];
    public function fields()
    {
        return $this->belongsToMany(Field::class);  
    }
   public function yearSemesterCourses()
    {
        return $this->hasMany(YearSemesterCourse::class);
   }
   public function choices()
    {
        return $this->hasMany(Choice::class);
    }
    public function instructors()
    {
        return $this->belongsToMany(Instructor::class,'instructor_courses','instructor_id')->withPivot('number_of_semesters','is_recent'); 
    }
    public function lectureRoomPreference()
    {
        return $this->hasOne(YearSemesterCourse::class, 'course_id')
                   ->select('id', 'course_id', 'preferred_lecture_room_id');
    }

    public function labRoomPreference()
    {
        return $this->hasOne(YearSemesterCourse::class, 'course_id')
                   ->select('id', 'course_id', 'preferred_lab_room_id');
    }
}
