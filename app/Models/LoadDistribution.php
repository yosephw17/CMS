<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoadDistribution extends Model
{
    use HasFactory;

    protected $fillable = [ 'year', 'semester_id', 'department_id'];

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }

    public function year()
    {
        return $this->belongsTo(Year::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function results()
    {
        return $this->hasMany(LoadDistributionResult::class);
    }
  
}