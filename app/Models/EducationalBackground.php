<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EducationalBackground extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',

    ];
    public function instructors()
    {
        return $this->belongsToMany(Instructor::class,'intructor_educational_background','instructor_id')->withPivot('instructor_id'); 
    }

}
