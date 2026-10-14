<?php

namespace App\Http\Controllers\Portal;

use App\Actions\SubmitBuyingRequestResponse;
use App\Http\Controllers\Controller;
use App\Models\BuyingRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BuyingRequestResponseController extends Controller
{
    public function store(Request $r, BuyingRequest $buyingRequest, SubmitBuyingRequestResponse $action): RedirectResponse
    {
        $data = $r->validate(['project_id' => ['required', 'exists:projects,id'], 'message' => ['nullable', 'string', 'max:3000']]);
        $project = Project::findOrFail($data['project_id']);
        $action->handle($buyingRequest, $project, $r->user(), $data['message'] ?? null);

        return back()->with('success', 'Project submitted to the buyer.');
    }
}
