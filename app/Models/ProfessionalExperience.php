<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfessionalExperience extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'field_id',
    ];

    /**
     * Get the field associated with the professional experience.
     */
    public function field()
    {
        return $this->belongsTo(Field::class);
    }
    public function instructor()
    {
        return $this->belongsToMany(Instructor::class);
    }
}
