<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationResponse extends Model
{
    protected $fillable = ['link_id', 'question_id', 'rating'];

    public function link()
    {
        return $this->belongsTo(EvaluationLink::class);
    }

    public function question()
    {
        return $this->belongsTo(EvaluationQuestion::class);
    }
    public function evaluationLink()
    {
        return $this->belongsTo(EvaluationLink::class, 'link_id');
    }
}