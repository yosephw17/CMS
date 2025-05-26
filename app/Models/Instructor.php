<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Instructor extends Model
{
    use HasFactory ,Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'role_id',
        'academic_rank',
        'department_id',
        'is_available',
        'is_studying',
        'is_approved',
        'is_mentor',
        'studying',
    ];

    public function role()
    {
        return $this->belongsTo(InstructorRole::class);
    }
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    public function choices()
    {
        return $this->hasMany(Choice::class);
    }
    public function professionalExperiences()
    {
        return $this->belongsToMany(ProfessionalExperience::class,'instructor_professional_experience','instructor_id','pro_exp_id');
    }
    public function educationalBackgrounds()
    {
        return $this->belongsToMany(EducationalBackground::class, 'instructor_educational_background', 'instructor_id', 'edu_background_id')
        ->withPivot('field_id');
    }
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'instructor_course', 'instructor_id', 'course_id')
        ->withPivot('is_recent', 'number_of_semesters');
    }

    public function researches()
    {
        return $this->hasMany(Research::class);
    }

    public function students()
{
    return $this->hasMany(Student::class, 'assigned_mentor_id');}


    public function results()
    {
        return $this->hasMany(Result::class);
    }

}
