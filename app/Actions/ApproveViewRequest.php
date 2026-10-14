<?php

namespace App\Actions;

use App\Models\AccessGrant;
use App\Models\User;
use App\Models\ViewRequest;
use App\Notifications\MarketplaceNotification;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;

class ApproveViewRequest
{
    public function __construct(private StartConversation $startConversation) {}

    public function handle(ViewRequest $viewRequest, User $actor, ?string $note = null): array
    {
        return DB::transaction(function () use ($viewRequest, $actor, $note) {
            abort_unless($viewRequest->project->isManagedBy($actor), 403);
            abort_unless($viewRequest->status === 'pending', 422);

            $viewRequest->forceFill([
                'status' => 'approved',
                'decision_note' => $note,
                'decided_at' => now(),
                'decided_by' => $actor->id,
            ])->save();
            $grant = AccessGrant::updateOrCreate(
                ['project_id' => $viewRequest->project_id, 'user_id' => $viewRequest->requester_id],
                [
                    'granted_by' => $actor->id,
                    'view_request_id' => $viewRequest->id,
                    'granted_at' => now(),
                    'expires_at' => $viewRequest->expires_at,
                    'revoked_at' => null,
                ]
            );
            $conversation = $this->startConversation->handle(
                'view_request',
                $viewRequest,
                $actor,
                [$viewRequest->requester, $viewRequest->project->owner]
            );
            AuditService::record('access.granted', $grant, ['view_request_id' => $viewRequest->id]);
            $viewRequest->requester->notify(new MarketplaceNotification(
                'Viewing request approved',
                'Your request for '.$viewRequest->project->title.' was approved. A private conversation is now available.',
                '/messages/'.$conversation->id,
                'success'
            ));

            return compact('grant', 'conversation');
        });
    }
}
