<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Research extends Model
{
    use HasFactory;
    protected $table = 'researches';
    protected $fillable = [
        'title',
        'field_id',
        'instructor_id',
        'link',
        'description',
    ];

    /**
     * Get the field associated with the research.
     */
    public function field()
    {
        return $this->belongsTo(Field::class);
    }

    /**
     * Get the instructor associated with the research.
     */
    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }
}
