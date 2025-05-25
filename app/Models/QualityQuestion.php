<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QualityQuestion extends Model
{
    use HasFactory;

    protected $table = 'quality_questions';

    protected $fillable = [
        'question_text',
        'input_type',
        'options',
        'audience' // Added audience field
    ];

    protected $casts = [
        'options' => 'array' // Automatically cast JSON to PHP array
    ];
}