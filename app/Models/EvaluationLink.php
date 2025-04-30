<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationLink extends Model
{
    protected $fillable = [
        'instructor_id',
        'student_email',
        'student_name',  // Add this
        'academic_year_id',
        'semester_id',
        'hash',
        'is_used'
    ];
    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }

    public function responses()
    {
        return $this->hasMany(EvaluationResponse::class,'link_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }
}