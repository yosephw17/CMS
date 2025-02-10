<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;
    protected $fillable = [
        'year',
        'semester',

    ];
    public function results()
    {
        return $this->hasMany(Result::class);
    }
}
