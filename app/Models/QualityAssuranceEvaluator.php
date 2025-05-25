<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QualityAssuranceEvaluator extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'instructor_id',
        'semester_id',
        'academic_year_id',
        'audit_session_id',
        'section',

    ];

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function auditSession()
    {
        return $this->belongsTo(AuditSession::class);
    }

    public function qualityLinks()
    {
        return $this->hasMany(QualityLink::class, 'evaluator_id');
    }
}
