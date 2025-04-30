<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuestInstructor extends Model
{
    use HasFactory;

    protected $fillable=['name','course_id', 'schedule_id'];
}
