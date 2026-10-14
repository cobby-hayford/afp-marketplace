<?php

namespace App\Actions;

use App\Models\ModerationAction;
use App\Models\Project;
use App\Models\User;
use App\Notifications\MarketplaceNotification;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;

class ApproveProject
{
    public function handle(Project $project, User $admin, ?string $comments = null): Project
    {
        return DB::transaction(function () use ($project, $admin, $comments) {
            abort_unless(in_array($project->publication_status, ['submitted', 'under_review', 'approved'], true), 422);
            $before = $project->publication_status;
            $project->forceFill([
                'publication_status' => 'published',
                'published_at' => now(),
            ])->save();
            ModerationAction::create([
                'moderatable_type' => $project->getMorphClass(),
                'moderatable_id' => $project->id,
                'actor_id' => $admin->id,
                'previous_status' => $before,
                'new_status' => 'published',
                'comments' => $comments,
            ]);
            AuditService::record('project.approved', $project, ['previous_status' => $before]);
            $project->owner->notify(new MarketplaceNotification(
                'Project approved',
                $project->title.' is now published in the Content Marketplace.',
                '/projects/'.$project->slug,
                'success'
            ));

            return $project->fresh();
        });
    }
}
