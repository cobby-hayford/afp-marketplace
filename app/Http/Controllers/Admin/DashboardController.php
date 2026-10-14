<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BuyingRequest;
use App\Models\CollaborationPost;
use App\Models\Opportunity;
use App\Models\Organisation;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Report;
use App\Models\User;
use App\Models\ViewRequest;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('admin/dashboard', ['stats' => ['users' => User::count(), 'approved_members' => Profile::where('status', 'approved')->count(), 'pending_members' => Profile::where('status', 'pending_review')->count(), 'producers' => User::where('account_type', 'producer')->count(), 'organisations' => Organisation::count(), 'projects' => Project::count(), 'pending_projects' => Project::whereIn('publication_status', ['submitted', 'under_review'])->count(), 'buying_requests' => BuyingRequest::count(), 'pending_buying_requests' => BuyingRequest::where('publication_status', 'submitted')->count(), 'collaboration' => CollaborationPost::count(), 'opportunities' => Opportunity::count(), 'view_requests' => ViewRequest::count(), 'reports' => Report::whereIn('status', ['open', 'under_review'])->count()], 'recentActions' => AuditLog::with('actor:id,first_name,last_name')->latest()->limit(12)->get()]);
    }
}
