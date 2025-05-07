<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'year_id', 'department_id', 'number_of_students'];
    public function year()
    {
        return $this->belongsTo(Year::class);
    }
    public function department() 
    {
         return $this->belongsTo(Department::class); 
    }

    public function stream() 
    {
         return $this->belongsTo(Stream::class); 
    }
    public function courses() 
    {
         return $this->hasMany(YearSemesterCourse::class);
    }

}
