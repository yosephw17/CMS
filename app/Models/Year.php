<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Year extends Model
{
    use HasFactory;
    protected $fillable = ['name'];

    public function yearSemesterCourses()
    {
        return $this->hasMany(YearSemesterCourse::class);
    }
    public function sections()
    {
        return $this->hasMany(Section::class);
    }
}
