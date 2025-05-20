<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stream extends Model
{
    use HasFactory;

    protected $fillable=[
        'name','department_id','year_id','semester_id'
    ];
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    public function year()
    {
        return $this->belongsTo(Year::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }
    public function courses()
    {
        return $this->hasMany(YearSemesterCourse::class);
    }
    public function sections()
    {
        return $this->hasMany(Section::class);
    }
}
