<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuyingRequestResponse extends Model
{
    protected $fillable = ['buying_request_id', 'user_id', 'project_id', 'message', 'status'];

    public function buyingRequest(): BelongsTo
    {
        return $this->belongsTo(BuyingRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
