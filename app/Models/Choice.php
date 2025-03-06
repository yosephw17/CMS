<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Choice extends Model
{
    use HasFactory;
    protected $table = 'choice';

    protected $fillable = [
        'instructor_id',
        'course_id',
        'assignment_id',
        'rank',

    ];
    public function instructor()
{
    return $this->belongsTo(Instructor::class);
}
public function course() {
    return $this->belongsTo(Course::class,'course_id');
}

public function assignment() {
    return $this->belongsTo(Assignment::class,'assignment_id');
}
}
