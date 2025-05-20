<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    use HasFactory;
    protected $fillable = ['name'];

    public function yearSemesterCourses()
    {
        return $this->hasMany(YearSemesterCourse::class);
    }
    public function streamStarts()
{
    return $this->hasMany(StreamStart::class);
}
}
