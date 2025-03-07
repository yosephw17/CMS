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
        'is_available',
        'is_studying',
        'is_approved',
    ];

    public function role()
    {
        return $this->belongsTo(InstructorRole::class);
    }
    public function choices()
    {
        return $this->hasMany(Choice::class); 
    }
    public function professionalExperiences()
    {
        return $this->belongsToMany(professionalExperience::class,'instructor_professional_experience','instructor_id','pro_exp_id');  
    }
    public function educationalBackgrounds()
    {
        return $this->belongsToMany(EducationalBackground::class, 'instructor_educational_background', 'instructor_id', relatedPivotKey: 'edu_background_id')
                    ->withTimestamps();
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
