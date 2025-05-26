<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoadDistributionResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'load_distribution_id',
        'instructor_id',
        'course_id',
        'section',
        'students_count',
        'assignment_type',
        'lecture_hours',
        'lab_hours',
        'tutorial_hours',
        'lecture_sections',
        'lab_sections',
        'tutorial_sections',
        'elh',
        'total_load',
        'over_under_load',
        'amount_paid',
        'expected_load',
    ];

    protected $casts = [
        'assignment_type' => 'string',
        'lecture_hours' => 'float',
        'lab_hours' => 'float',
        'tutorial_hours' => 'float',
        'elh' => 'float',
        'total_load' => 'float',
        'over_under_load' => 'float',
        'amount_paid' => 'float',
    ];

    public function loadDistribution()
    {
        return $this->belongsTo(LoadDistribution::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    public function instructor()    
    {
        return $this->belongsTo(Instructor::class);
    }

}