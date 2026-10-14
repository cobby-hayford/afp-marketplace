<?php

namespace App\Actions;

use App\Models\CopyrightDeclaration;
use App\Models\ModerationAction;
use App\Models\Project;
use App\Models\User;
use App\Notifications\MarketplaceNotification;
use Illuminate\Support\Facades\DB;

class SubmitProject
{
    public const DECLARATION = 'I confirm that I own, represent, or have sufficient authority to list this project and its associated materials on African Film Producers Content Marketplace.';

    public function handle(Project $project, User $user, ?string $ip): Project
    {
        return DB::transaction(function () use ($project, $user, $ip) {
            abort_unless($project->isManagedBy($user), 403);
            abort_unless(in_array($project->publication_status, ['draft', 'changes_requested', 'rejected'], true), 422);
            $before = $project->publication_status;
            $project->forceFill(['publication_status' => 'submitted', 'submitted_at' => now()])->save();
            CopyrightDeclaration::create(['project_id' => $project->id, 'user_id' => $user->id, 'declaration' => self::DECLARATION, 'ip_address' => $ip, 'accepted_at' => now()]);
            ModerationAction::create(['moderatable_type' => $project->getMorphClass(), 'moderatable_id' => $project->id, 'actor_id' => $user->id, 'previous_status' => $before, 'new_status' => 'submitted', 'comments' => 'Submitted by project owner.']);
            User::query()->whereIn('role', ['administrator', 'super_administrator'])->each(fn ($admin) => $admin->notify(new MarketplaceNotification('Project submitted', $project->title.' is awaiting moderation.', '/admin/projects')));

            return $project->fresh();
        });
    }
}
