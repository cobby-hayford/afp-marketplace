<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditService
{
    public static function record(string $action, ?Model $entity = null, array $metadata = [], ?Request $request = null): AuditLog
    {
        return AuditLog::create(['actor_id' => auth()->id(), 'action' => $action, 'entity_type' => $entity?->getMorphClass(), 'entity_id' => $entity?->getKey(), 'metadata' => $metadata ?: null, 'ip_address' => $request?->ip() ?? request()?->ip()]);
    }
}
