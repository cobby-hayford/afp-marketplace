<?php

namespace App\Actions;

use App\Models\Organisation;
use App\Models\Project;
use App\Models\User;
use App\Models\ViewRequest;
use App\Notifications\MarketplaceNotification;
use Illuminate\Support\Facades\DB;

class RequestProjectAccess
{
    public function handle(Project $project, User $requester, array $data): ViewRequest
    {
        return DB::transaction(function () use ($project, $requester, $data) {
            abort_unless($project->published()->whereKey($project->id)->exists(), 404);
            abort_if($project->isManagedBy($requester), 422, 'You already manage this project.');

            $existing = ViewRequest::where('requester_id', $requester->id)->where('project_id', $project->id)->first();
            abort_if($existing && in_array($existing->status, ['pending', 'approved'], true), 422, 'You already have an active request for this project.');

            if ($data['organisation_id'] ?? null) {
                $organisation = Organisation::findOrFail($data['organisation_id']);
                abort_unless($organisation->managedBy($requester) && $organisation->approval_status === 'approved', 403);
            }

            $viewRequest = ViewRequest::updateOrCreate(
                ['requester_id' => $requester->id, 'project_id' => $project->id],
                [...$data, 'status' => 'pending', 'decision_note' => null, 'decided_at' => null, 'decided_by' => null]
            );
            $project->owner->notify(new MarketplaceNotification(
                'New Request to View',
                $requester->name.' requested restricted access to '.$project->title.'.',
                '/view-requests',
                'request'
            ));

            return $viewRequest;
        });
    }
}
