<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;
    protected $fillable = [
        'year',
        'semester_id',
        'department_id',
        'stream_id'

    ];
    public function results()
    {
        return $this->hasMany(Result::class);
    }
    public function choices()
    {
        return $this->hasMany(Choice::class,'assignment_id');
    }
}
