<?php

namespace Database\Seeders;

use App\Models\BuyingRequest;
use App\Models\CollaborationPost;
use App\Models\CollaborationType;
use App\Models\ContentType;
use App\Models\Country;
use App\Models\Genre;
use App\Models\NewsCategory;
use App\Models\NewsPost;
use App\Models\Opportunity;
use App\Models\OpportunityType;
use App\Models\Organisation;
use App\Models\Profile;
use App\Models\Project;
use App\Models\ProjectStage;
use App\Models\ProjectTag;
use App\Models\Stand;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(DatabaseSeeder::class);
        $ghana = Country::where('iso_code', 'GH')->firstOrFail();
        $nigeria = Country::where('iso_code', 'NG')->firstOrFail();
        $kenya = Country::where('iso_code', 'KE')->firstOrFail();
        $southAfrica = Country::where('iso_code', 'ZA')->firstOrFail();
        $senegal = Country::where('iso_code', 'SN')->firstOrFail();
        $countries = [$ghana, $nigeria, $kenya, $southAfrica, $senegal];
        $users = [];
        foreach ([['Ama', 'Mensah', 'producer', $ghana], ['Chinedu', 'Okafor', 'producer', $nigeria], ['Wanjiku', 'Njeri', 'producer', $kenya], ['Thabo', 'Dlamini', 'producer', $southAfrica], ['Awa', 'Diop', 'producer', $senegal], ['Maya', 'Buyer', 'broadcaster', $ghana]] as $i => [$first,$last,$type,$country]) {
            $u = User::firstOrCreate(['email' => strtolower($first).'.demo@example.test'], ['first_name' => $first, 'last_name' => $last, 'password' => Hash::make('demo-password'), 'country_id' => $country->id, 'account_type' => $type, 'email_verified_at' => now()]);
            $u->forceFill(['email_verified_at' => now()])->save();
            $p = Profile::firstOrCreate(['user_id' => $u->id], ['slug' => Str::slug($first.'-'.$last).'-demo', 'professional_name' => $first.' '.$last, 'country_id' => $country->id, 'short_bio' => 'African screen and digital content professional working across development, production and international collaboration.', 'biography' => 'Demo professional profile created only for local development and acceptance testing.', 'status' => 'approved', 'is_public' => true, 'verification_status' => 'verified', 'approved_at' => now()]);
            $p->forceFill(['verification_status' => 'verified'])->save();
            $p->roles()->firstOrCreate(['role' => $type === 'producer' ? 'Producer' : 'Content Buyer']);
            $users[] = $u;
        }$company = Organisation::firstOrCreate(['slug' => 'golden-tree-productions-demo'], ['created_by' => $users[0]->id, 'name' => 'Golden Tree Productions', 'type' => 'Production Company', 'country_id' => $ghana->id, 'description' => 'Independent Ghanaian production company developing premium African stories for international audiences.', 'verification_status' => 'verified', 'approval_status' => 'approved', 'is_public' => true]);
        $company->forceFill(['verification_status' => 'verified', 'approval_status' => 'approved', 'is_public' => true])->save();
        $company->members()->syncWithoutDetaching([$users[0]->id => ['role' => 'owner']]);
        $stand = Stand::firstOrCreate(['slug' => 'golden-tree-productions-demo'], ['created_by' => $users[0]->id, 'organisation_id' => $company->id, 'display_name' => 'Golden Tree Productions', 'stand_type' => 'Production Company Stand', 'country_id' => $ghana->id, 'summary' => 'Original Ghanaian stories built for African and global audiences.', 'description' => 'Meet the team and explore projects seeking financing, co-production and distribution.', 'offerings' => 'Feature films, documentary development, co-production services.', 'looking_for' => 'Funding, distributors, sales agents and co-production partners.', 'verification_status' => 'verified', 'is_public' => true, 'featured' => true]);
        $stand->forceFill(['verification_status' => 'verified'])->save();
        $feature = ContentType::where('name', 'Feature Film')->firstOrFail();
        $development = ProjectStage::where('name', 'Development')->firstOrFail();
        $drama = Genre::where('name', 'Drama')->firstOrFail();
        $funding = ProjectTag::where('name', 'Funding')->firstOrFail();
        $coproducer = ProjectTag::where('name', 'Co-Producer')->firstOrFail();
        $p = Project::firstOrCreate(['slug' => 'tindana'], ['owner_id' => $users[0]->id, 'organisation_id' => $company->id, 'stand_id' => $stand->id, 'title' => 'Tindana', 'logline' => 'A young woman returns to northern Ghana and confronts a family legacy that could reshape her future.', 'short_synopsis' => 'A character-led Ghanaian drama about inheritance, identity and the cost of belonging.', 'description' => 'Tindana is a development-stage feature film designed for regional and international co-production.', 'content_type_id' => $feature->id, 'country_id' => $ghana->id, 'project_stage_id' => $development->id, 'production_year' => now()->year + 1, 'budget' => 200000, 'currency' => 'USD', 'rights_available' => 'International sales, broadcast and streaming rights subject to negotiation.', 'territories' => 'Worldwide excluding any territory contracted later.', 'screener_url' => 'https://example.test/restricted-screener-demo', 'publication_status' => 'published', 'published_at' => now(), 'featured' => true]);
        $p->genres()->sync([$drama->id]);
        $p->tags()->sync([$funding->id, $coproducer->id]);
        foreach (array_slice($users, 1, 4) as $i => $u) {
            $country = $countries[$i + 1];
            $proj = Project::firstOrCreate(['slug' => 'demo-project-'.$country->slug], ['owner_id' => $u->id, 'title' => 'Stories from '.$country->name, 'logline' => 'A premium demo project from '.$country->name.' seeking international partners.', 'short_synopsis' => 'Development example for marketplace discovery and filtering.', 'description' => 'Demo-only listing used to validate project browsing across African markets.', 'content_type_id' => $feature->id, 'country_id' => $country->id, 'project_stage_id' => $development->id, 'budget' => 150000 + ($i * 50000), 'currency' => 'USD', 'publication_status' => 'published', 'published_at' => now()->subDays($i + 1)]);
            $proj->genres()->sync([$drama->id]);
            $proj->tags()->sync([$funding->id]);
        }$buyerOrg = Organisation::firstOrCreate(['slug' => 'africavision-tv-demo'], ['created_by' => $users[5]->id, 'name' => 'AfricaVision TV', 'type' => 'Broadcaster', 'country_id' => $ghana->id, 'description' => 'Demo television buyer.', 'approval_status' => 'approved', 'verification_status' => 'verified', 'is_public' => true]);
        $buyerOrg->forceFill(['verification_status' => 'verified', 'approval_status' => 'approved', 'is_public' => true])->save();
        $buyerOrg->members()->syncWithoutDetaching([$users[5]->id => ['role' => 'owner']]);
        $br = BuyingRequest::firstOrCreate(['slug' => 'west-african-drama-series-demo'], ['user_id' => $users[5]->id, 'organisation_id' => $buyerOrg->id, 'title' => 'West African Drama Series for Primetime', 'description' => 'Seeking completed or late-stage Ghanaian and Nigerian drama series for broadcast consideration.', 'rights_sought' => 'Broadcast rights', 'territories' => 'Ghana and selected West African territories', 'deadline' => now()->addMonths(3), 'publication_status' => 'published', 'published_at' => now()]);
        $br->contentTypes()->sync([ContentType::where('name', 'TV Series')->first()->id]);
        $br->countries()->sync([$ghana->id, $nigeria->id]);
        $collab = CollaborationPost::firstOrCreate(['slug' => 'co-producer-for-ghana-feature-demo'], ['user_id' => $users[0]->id, 'project_id' => $p->id, 'collaboration_type_id' => CollaborationType::where('name', 'Co-Producer')->first()->id, 'country_id' => $ghana->id, 'content_type_id' => $feature->id, 'genre_id' => $drama->id, 'title' => 'Co-Producer for Ghanaian Feature', 'description' => 'Seeking an experienced African or international co-producer for Tindana.', 'closing_date' => now()->addMonths(4), 'publication_status' => 'published', 'published_at' => now()]);
        Opportunity::firstOrCreate(['slug' => 'pan-african-film-development-grant-demo'], ['created_by' => $users[5]->id, 'opportunity_type_id' => OpportunityType::where('name', 'Film Grant')->first()->id, 'country_id' => $ghana->id, 'organisation' => 'Demo Film Fund', 'title' => 'Pan-African Film Development Grant', 'description' => 'Development support for African-led feature projects. Demo listing only.', 'eligibility' => 'African producers with a feature film in development.', 'deadline' => now()->addMonths(2), 'external_application_url' => 'https://example.org/apply', 'funding_amount' => 25000, 'currency' => 'USD', 'featured' => true, 'publication_status' => 'published', 'published_at' => now()]);
        NewsPost::firstOrCreate(['slug' => 'african-content-marketplace-opens-demo'], ['author_id' => $users[5]->id, 'news_category_id' => NewsCategory::where('name', 'Association News')->first()->id, 'title' => 'African Content Marketplace Opens for Industry Discovery', 'excerpt' => 'A demo news story showing the publication workflow.', 'content' => 'This development-only article demonstrates the News module and SEO-ready public article pages.', 'status' => 'published', 'published_at' => now()]);
    }
}
