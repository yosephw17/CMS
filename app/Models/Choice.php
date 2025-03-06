<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Choice extends Model
{
    use HasFactory;
    protected $table = 'choice';

    protected $fillable = [
        'rank', 'assignment_id', 'instructor_id', 'course_id', 

    ];
    public function instructor()
{
    return $this->belongsTo(Instructor::class);
}
public function course() {
    return $this->belongsTo(Course::class);
}

public function assignment() {
    return $this->belongsTo(Assignment::class);
}
}
