<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\BuyingRequest;
use App\Models\Favourite;
use App\Models\Opportunity;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Stand;
use App\Services\MarketplacePresenter as P;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class FavouriteController extends Controller
{
    private array $map = [
        'project' => Project::class,
        'profile' => Profile::class,
        'stand' => Stand::class,
        'buying_request' => BuyingRequest::class,
        'opportunity' => Opportunity::class,
    ];

    public function index(Request $request): Response
    {
        $page = Favourite::where('user_id', $request->user()->id)->with('favouritable')->latest()->paginate(30);
        $page->through(function (Favourite $favourite) use ($request) {
            $target = $favourite->favouritable;
            if (! $target) {
                return null;
            }

            if ($target instanceof Project) {
                abort_unless($this->canSaveProject($target, $request->user()), 404);
                $target->loadMissing(['country', 'contentType', 'stage', 'genres', 'tags', 'festivalRecognitions']);

                return ['favourite_id' => $favourite->id, 'type' => 'project', 'item' => P::projectCard($target)];
            }
            if ($target instanceof Profile) {
                abort_unless($target->status === 'approved' && $target->is_public, 404);
                $target->loadMissing(['user', 'country', 'contentTypes', 'genres', 'languages', 'roles']);
                $target->loadCount(['projects as published_projects_count' => fn ($q) => $q->publiclyVisible()]);

                return ['favourite_id' => $favourite->id, 'type' => 'profile', 'item' => P::profileCard($target)];
            }
            if ($target instanceof Stand) {
                abort_unless($target->is_public, 404);
                $target->loadMissing('country');

                return ['favourite_id' => $favourite->id, 'type' => 'stand', 'item' => P::standCard($target)];
            }
            if ($target instanceof BuyingRequest) {
                abort_unless($target->publication_status === 'published' && $target->published_at?->lte(now()), 404);

                return ['favourite_id' => $favourite->id, 'type' => 'buying_request', 'item' => ['id' => $target->id, 'title' => $target->title, 'slug' => $target->slug, 'deadline' => $target->deadline]];
            }
            if ($target instanceof Opportunity) {
                abort_unless($target->publication_status === 'published' && $target->published_at?->lte(now()), 404);

                return ['favourite_id' => $favourite->id, 'type' => 'opportunity', 'item' => ['id' => $target->id, 'title' => $target->title, 'slug' => $target->slug, 'deadline' => $target->deadline]];
            }

            return null;
        });

        return Inertia::render('portal/saved', ['items' => $page]);
    }

    public function toggle(Request $request): RedirectResponse
    {
        $data = $request->validate(['type' => ['required', Rule::in(array_keys($this->map))], 'id' => ['required', 'integer', 'min:1']]);
        $class = $this->map[$data['type']];
        $target = $class::findOrFail($data['id']);

        // Saving does not bypass visibility rules. Users may only save currently public targets.
        $public = match ($data['type']) {
            'project' => $this->canSaveProject($target, $request->user()),
            'profile' => $target->status === 'approved' && $target->is_public,
            'stand' => $target->is_public,
            'buying_request', 'opportunity' => $target->publication_status === 'published' && $target->published_at?->lte(now()),
        };
        abort_unless($public, 404);

        $attrs = ['user_id' => $request->user()->id, 'favouritable_type' => $target->getMorphClass(), 'favouritable_id' => $target->getKey()];
        $existing = Favourite::where($attrs)->first();
        $existing ? $existing->delete() : Favourite::create($attrs);

        return back();
    }

    private function canSaveProject(Project $project, $user): bool
    {
        if ($project->publication_status !== 'published' || ! $project->published_at?->lte(now())) {
            return false;
        }
        if ($project->visibility === 'public') {
            return true;
        }

        return $project->visibility === 'members'
            && $user
            && ! $user->suspended_at
            && $user->hasVerifiedEmail()
            && $user->profile?->status === 'approved';
    }
}
