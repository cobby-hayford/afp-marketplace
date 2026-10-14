<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollaborationResponse extends Model
{
    protected $fillable = ['collaboration_post_id', 'user_id', 'message'];
}
