<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // ✅ Correct import

class Day extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function timeSlots(): HasMany

    {
        return $this->hasMany(TimeSlot::class);
    }
}
