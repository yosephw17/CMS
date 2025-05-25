<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['name'];
    protected $attributes = [
        'guard_name' => 'sanctum',
    ];


    public function permissions()
{
    return $this->belongsToMany(Permission::class);
}

}

