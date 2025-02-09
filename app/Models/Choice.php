<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Choice extends Model
{
    use HasFactory;
    protected $table = 'choice';

    protected $fillable = [
        'rank',

    ];
    public function instructor()
{
    return $this->belongsTo(Instructor::class);
}
}
