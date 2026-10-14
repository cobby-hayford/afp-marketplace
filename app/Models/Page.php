<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = ['title', 'slug', 'content', 'seo_title', 'meta_description', 'status'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
