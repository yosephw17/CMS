<?php

// app/Models/Evaluator.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;


class Evaluator extends Model
{
    use HasFactory, Notifiable;

    public const TYPE_STUDENT = 'student';
    public const TYPE_INSTRUCTOR = 'instructor';
    public const TYPE_DEAN = 'dean'; // Added dean type

    protected $fillable = [
        'email',
        'name',
        'type',
        'meta'
    ];

    protected $casts = [
        'meta' => 'array'
    ];

    public function evaluationLinks()
    {
        return $this->hasMany(EvaluationLink::class);
    }

    // Helper methods for evaluator types
    public function isStudent(): bool
    {
        return $this->type === self::TYPE_STUDENT;
    }

    public function isInstructor(): bool
    {
        return $this->type === self::TYPE_INSTRUCTOR;
    }



    // Added method for dean
    public function isDean(): bool
    {
        return $this->type === self::TYPE_DEAN;
    }

    // Helper to get all possible types
    public static function getTypes(): array
    {
        return [
            self::TYPE_STUDENT,
            self::TYPE_INSTRUCTOR,
            self::TYPE_DEAN
        ];
    }
}
