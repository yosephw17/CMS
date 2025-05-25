<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class QualityLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'audit_session_id',
        'instructor_id',
        'semester_id',
        'academic_year_id',
        'hash',
        'is_used',
        'evaluator_id', // Add evaluator_id to fillable attributes
        'is_self_evaluation',
        'section',
        'department_id',
        'courses_id'
    ];

    protected static function boot()
    {
        parent::boot();

        // Auto-generate hash when creating a new link
        static::creating(function ($model) {
            $model->hash = $model->hash ?? Str::random(40);
        });
    }

    // Relationships
    public function auditSession()
    {
        return $this->belongsTo(AuditSession::class);
    }

    public function instructor()
    {
        return $this->belongsTo(Instructor::class, 'instructor_id');
    }



    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }


    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
    public function course()
    {
        return $this->belongsTo(Course::class,'courses_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }


    // Add to your relationships
public function evaluator()
{
    return $this->belongsTo(QualityAssuranceEvaluator::class);
}
}