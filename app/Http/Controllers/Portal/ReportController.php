<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReportRequest;
use App\Models\Message;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Report;
use App\Models\Stand;
use Illuminate\Http\RedirectResponse;

class ReportController extends Controller
{
    public function store(ReportRequest $request): RedirectResponse
    {
        $map = [
            'project' => Project::class,
            'profile' => Profile::class,
            'stand' => Stand::class,
            'message' => Message::class,
        ];

        $class = $map[$request->validated('target_type')];
        $target = $class::findOrFail($request->validated('target_id'));
        $user = $request->user();

        $reportable = match (true) {
            $target instanceof Project => $target->publiclyVisible()->whereKey($target->id)->exists()
                || ($target->published()->whereKey($target->id)->where('visibility', 'members')->exists()
                    && $user->hasVerifiedEmail() && $user->profile?->status === 'approved'),
            $target instanceof Profile => $target->status === 'approved' && $target->is_public,
            $target instanceof Stand => $target->is_public,
            $target instanceof Message => $target->conversation->participants()->whereKey($user->id)->exists(),
            default => false,
        };

        abort_unless($reportable, 404);

        Report::create([
            'reporter_id' => $user->id,
            'reportable_type' => $target->getMorphClass(),
            'reportable_id' => $target->id,
            'reason_category' => $request->validated('reason_category'),
            'description' => $request->validated('description'),
        ]);

        return back()->with('success', 'Report submitted for review.');
    }
}
