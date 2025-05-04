<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'code',          // Room code/short identifier (e.g., "LH-101")
        'type',          // 'lecture', 'lab', 'seminar', etc.
        'capacity',
        'building_id',

    ];
    public function building()
    {
        return $this->belongsTo(Building::class);
    }
    public function scheduleResults()
    {
        return $this->hasMany(ScheduleResult::class);
    }

}
