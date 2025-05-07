<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;
    protected $fillable = [
        'year',
        'semester_id',
    ]; 
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }
    public function department()
    {
        return $this->belongsTo(Department::class);
    } 
    public function scheduleResults()
    {
        return $this->hasMany(ScheduleResult::class);
    }

    public function results()
    {
        return $this->hasMany(ScheduleResult::class);
    }
    
}
