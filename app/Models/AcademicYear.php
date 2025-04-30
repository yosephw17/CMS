<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',

    ];

    /**
     * Get the semesters for this academic year
     */
    public function semesters(): HasMany
    {
        return $this->hasMany(Semester::class);
    }

    /**
     * Get the evaluation links for this academic year
     */
    public function evaluationLinks(): HasMany
    {
        return $this->hasMany(EvaluationLink::class);
    }

    /**
     * Scope a query to only include current academic year
     */



}