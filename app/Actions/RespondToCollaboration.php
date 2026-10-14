<?php

namespace App\Actions;

use App\Models\CollaborationPost;
use App\Models\CollaborationResponse;
use App\Models\User;
use App\Notifications\MarketplaceNotification;
use Illuminate\Support\Facades\DB;

class RespondToCollaboration
{
    public function __construct(private StartConversation $startConversation) {}

    public function handle(CollaborationPost $post, User $user, ?string $message): CollaborationResponse
    {
        return DB::transaction(function () use ($post, $user, $message) {
            abort_unless($post->published()->whereKey($post->id)->exists(), 404);
            abort_if($post->closing_date && $post->closing_date->lt(today()), 422, 'This collaboration has closed.');
            abort_if($post->user_id === $user->id, 422, 'You cannot respond to your own collaboration post.');
            $response = CollaborationResponse::create(['collaboration_post_id' => $post->id, 'user_id' => $user->id, 'message' => $message]);
            $conversation = $this->startConversation->handle('collaboration', $post, $user, [$post->user]);
            $post->user->notify(new MarketplaceNotification('New collaboration response', $user->name.' responded to '.$post->title.'.', '/messages/'.$conversation->id));

            return $response;
        });
    }
}
