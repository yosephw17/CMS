<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationCategory extends Model
{
    protected $fillable = ['name', 'order'];

    public function questions(): HasMany
    {
        return $this->hasMany(EvaluationQuestion::class, 'category_id');
        // Must match the foreign key column name exactly
    }
}