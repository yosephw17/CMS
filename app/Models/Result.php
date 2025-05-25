<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    use HasFactory;
    protected $fillable=[

    'point','is_assigned','instructor_id','course_id','assignment_id','stream_id','type'
    ];
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }
    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }
    
    public function stream()
    {
        return $this->belongsTo(Stream::class);
    }
}
