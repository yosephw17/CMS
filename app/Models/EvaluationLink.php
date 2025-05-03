<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationLink extends Model
{
    protected $fillable = [
        'instructor_id',
        'evaluator_id',  // Changed from student_email/name
        'academic_year_id',
        'semester_id',
        'hash',
        'is_used',
        'completed_at'  // Recommended for tracking
    ];

    // Relationships
    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }

    public function evaluator()
    {
        return $this->belongsTo(Evaluator::class);
    }

    public function responses()
    {
        return $this->hasMany(EvaluationResponse::class, 'link_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    // Scopes
    public function scopeUsed($query)
    {
        return $query->where('is_used', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_used', false);
    }
}