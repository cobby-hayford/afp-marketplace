<?php

namespace App\Http\Middleware;

use App\Models\BuyingRequest;
use App\Models\CollaborationPost;
use App\Models\FestivalRecognition;
use App\Models\Organisation;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Report;
use App\Services\SiteSettings;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $user = $request->user();
        $settings = SiteSettings::all();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user?->only(['id', 'first_name', 'last_name', 'email', 'role', 'account_type', 'country_id']),
                'profile_status' => $user?->profile?->status,
            ],
            'notifications' => [
                'unread_count' => $user?->unreadNotifications()->count() ?? 0,
                'recent' => $user?->notifications()->latest()->limit(6)->get()->map(fn ($notification) => [
                    'id' => $notification->id,
                    'data' => $notification->data,
                    'read_at' => $notification->read_at?->toIso8601String(),
                    'created_at' => $notification->created_at?->diffForHumans(),
                ]) ?? [],
            ],
            'flash' => ['success' => fn () => session('success'), 'error' => fn () => session('error')],
            'site' => [
                'name' => $settings['brand_name'],
                'subtitle' => $settings['marketplace_subtitle'],
                'tagline' => $settings['tagline'],
                'disclaimer' => $settings['platform_disclaimer'],
                'contact_email' => $settings['contact_email'],
                'contact_phone' => $settings['contact_phone'],
                'social' => [
                    'facebook' => $settings['facebook_url'],
                    'instagram' => $settings['instagram_url'],
                    'linkedin' => $settings['linkedin_url'],
                    'x' => $settings['x_url'],
                ],
            ],
            'adminCounts' => ($user?->isAdmin() ? [
                'memberships' => Profile::where('status', 'pending_review')->count(),
                'projects' => Project::whereIn('publication_status', ['submitted', 'under_review'])->count(),
                'organisations' => Organisation::whereIn('approval_status', ['submitted', 'pending_review'])->count(),
                'buying_requests' => BuyingRequest::where('publication_status', 'submitted')->count(),
                'collaboration' => CollaborationPost::where('publication_status', 'submitted')->count(),
                'verifications' => Profile::whereIn('verification_status', ['self_declared', 'pending_verification'])->count() + Organisation::whereIn('verification_status', ['self_declared', 'pending_verification'])->count() + FestivalRecognition::whereIn('verification_status', ['self_declared', 'pending_verification'])->count(),
                'reports' => Report::whereIn('status', ['open', 'under_review'])->count(),
            ] : []),
        ];
    }
}
