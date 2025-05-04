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
}
