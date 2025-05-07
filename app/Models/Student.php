<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    public function instructor()
{
    return $this->belongsTo(Instructor::class, 'assigned_mentor_id');}

protected $fillable = [
    'full_name',
    'email',
    'phone_number',
    'department_id',
    'assigned_mentor_id',
    'sex',
    'hosting_company',
    'location',
];
}
