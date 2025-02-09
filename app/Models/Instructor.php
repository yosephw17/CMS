<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Instructor extends Model
{
    use HasFactory;

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
        return $this->belongsToMany(professionalExperience::class);  
    }
    public function educationalBackgrounds()
    {
        return $this->belongsToMany(EducationalBackground::class, 'instructor_educational_background', 'instructor_id', 'edu_background_id')
                    ->withTimestamps();
    }   

    public function researches()
    {
        return $this->hasMany(Research::class); 
    }

}
