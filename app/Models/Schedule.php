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
    public function scheduleResults()
    {
        return $this->hasMany(ScheduleResult::class);
    }

    public function results()
    {
        return $this->hasMany(ScheduleResult::class);
    }
    
}
