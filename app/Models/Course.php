<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'course_code',
        'cp',
        'lecture_cp',
        'lab_cp',
    ];
    public function fields()
    {
        return $this->belongsToMany(Field::class);  
    }
}
