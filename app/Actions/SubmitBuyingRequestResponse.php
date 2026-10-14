<?php

namespace App\Actions;

use App\Models\BuyingRequest;
use App\Models\BuyingRequestResponse;
use App\Models\Project;
use App\Models\User;
use App\Notifications\MarketplaceNotification;
use Illuminate\Support\Facades\DB;

class SubmitBuyingRequestResponse
{
    public function handle(BuyingRequest $request, Project $project, User $user, ?string $message): BuyingRequestResponse
    {
        return DB::transaction(function () use ($request, $project, $user, $message) {
            abort_unless($request->published()->whereKey($request->id)->exists(), 404);
            abort_if($request->deadline && $request->deadline->lt(today()), 422, 'This buying request has closed.');
            abort_unless($project->isManagedBy($user) && $project->published()->whereKey($project->id)->exists(), 403);
            $response = BuyingRequestResponse::create(['buying_request_id' => $request->id, 'user_id' => $user->id, 'project_id' => $project->id, 'message' => $message]);
            $request->user->notify(new MarketplaceNotification('New buying request response', $user->name.' responded to '.$request->title.' with '.$project->title.'.', '/buying-requests/'.$request->slug));

            return $response;
        });
    }
}
