<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QualityResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'quality_link_id',
        'question_id',
        'answer'
    ];

    protected $casts = [
        'answer' => 'array' // Auto-convert JSON answers to arrays
    ];

    // Relationships
    public function qualityLink()
    {
        return $this->belongsTo(QualityLink::class);
    }

    public function question()
    {
        return $this->belongsTo(QualityQuestion::class);
    }

    // Helper method to format answers based on question type
    public function getFormattedAnswer()
    {
        return match($this->question->input_type) {
            'number' => (int) $this->answer,
            'dropdown' => is_array($this->answer) ? $this->answer : [$this->answer],
            default => $this->answer
        };
    }
}