<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'type'];

    protected $casts = [
        'type' => 'string',
    ];

    public function items()
    {
        return $this->hasMany(Item::class);
    }
}
