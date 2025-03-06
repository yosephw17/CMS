<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SignedToken extends Model
{
    use HasFactory;

    protected $fillable = ['instructor_id', 'token', 'expires_at'];

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }
}
