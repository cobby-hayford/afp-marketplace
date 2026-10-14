<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Project;
use App\Models\ViewRequest;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $user = request()->user();
        $projects = Project::managedBy($user);
        $managedProjectIds = (clone $projects)->select('projects.id');

        return Inertia::render('portal/dashboard', [
            'stats' => [
                'published' => (clone $projects)->published()->count(),
                'pending' => (clone $projects)->whereIn('publication_status', ['submitted', 'under_review'])->count(),
                'draft' => (clone $projects)->where('publication_status', 'draft')->count(),
                'view_requests' => ViewRequest::whereIn('project_id', clone $managedProjectIds)->where('status', 'pending')->count(),
                'unread_messages' => Conversation::whereHas('participants', fn ($q) => $q->where('users.id', $user->id))
                    ->whereExists(function ($q) use ($user) {
                        $q->selectRaw('1')->from('conversation_participants as cp')
                            ->whereColumn('cp.conversation_id', 'conversations.id')
                            ->where('cp.user_id', $user->id)
                            ->where(fn ($x) => $x->whereNull('cp.last_read_at')->orWhereColumn('conversations.last_message_at', '>', 'cp.last_read_at'));
                    })->count(),
                'saved_projects' => $user->favourites()->where('favouritable_type', (new Project)->getMorphClass())->count(),
            ],
            'recentProjects' => Project::managedBy($user)->latest()->limit(5)->get(['id', 'title', 'slug', 'publication_status', 'updated_at']),
            'recentRequests' => ViewRequest::with(['requester:id,first_name,last_name', 'project:id,title,slug,owner_id'])
                ->whereIn('project_id', Project::managedBy($user)->select('projects.id'))->latest()->limit(5)->get(),
        ]);
    }
}
