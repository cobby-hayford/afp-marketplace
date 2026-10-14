<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\BuyingRequest;
use App\Models\CollaborationPost;
use App\Models\Opportunity;
use App\Models\Project;
use App\Models\Stand;
use Inertia\Inertia;
use Inertia\Response;

class TradeHallController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('public/trade-hall', ['counts' => ['projects' => Project::publiclyVisible()->count(), 'stands' => Stand::public()->count(), 'buying_requests' => BuyingRequest::published()->count(), 'collaboration' => CollaborationPost::published()->count(), 'opportunities' => Opportunity::published()->count()], 'seo' => ['title' => 'The African Content Trade Hall', 'description' => 'Discover content, meet producers, find buyers and build partnerships.', 'canonical' => url('/trade-hall')]]);
    }
}
