<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationQuestion extends Model
{
    protected $fillable = [
        'category_id',
        'question',
        'order',
        'type',
        'target_role' // New field to specify which instructor role this question applies to
    ];

    // Define question types as constants
    public const TYPE_GENERAL = 'general';
    public const TYPE_STUDENT = 'student';
    public const TYPE_INSTRUCTOR = 'instructor';
    public const TYPE_DEAN = 'dean';

    // Define target roles
    public const TARGET_LAB_ASSISTANT = 'lab_assistant';
    public const TARGET_REGULAR_INSTRUCTOR = 'regular_instructor';
    // Add more roles as needed

    public function category(): BelongsTo
    {
        return $this->belongsTo(EvaluationCategory::class, 'category_id');
    }

    // Scopes for filtering
    public function scopeForStudent($query)
    {
        return $query->whereIn('type', [self::TYPE_GENERAL, self::TYPE_STUDENT]);
    }

    public function scopeForInstructor($query)
    {
        return $query->whereIn('type', [self::TYPE_GENERAL, self::TYPE_INSTRUCTOR]);
    }

    public function scopeForDean($query)
    {
        return $query->whereIn('type', [self::TYPE_GENERAL, self::TYPE_DEAN]);
    }

    // New scopes for target roles


    public function scopeForLabAssistant($query)
    {
        return $this->scopeForTargetRole($query, self::TARGET_LAB_ASSISTANT);
    }

    public function scopeForRegularInstructor($query)
    {
        return $this->scopeForTargetRole($query, self::TARGET_REGULAR_INSTRUCTOR);
    }
    // In EvaluationQuestion model
public function scopeForInstructorRole($query, $roleId)
{
    $targetRole = ($roleId == Role::LAB_ASSISTANT)
        ? self::TARGET_LAB_ASSISTANT
        : self::TARGET_REGULAR_INSTRUCTOR;

    return $query->where('target_role', $targetRole);
}
}