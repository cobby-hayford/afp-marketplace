<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = ['name', 'iso_code', 'slug', 'region', 'is_african'];

    protected function casts(): array
    {
        return ['is_african' => 'boolean'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
