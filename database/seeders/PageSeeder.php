<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = ['about' => ['About African Film Producers', 'African Film Producers Content Marketplace connects creators and professional buyers across Africa and the world.'], 'terms' => ['Terms of Use', 'Use of this marketplace is subject to responsible professional conduct, lawful representation of rights, and applicable platform rules.'], 'privacy' => ['Privacy Policy', 'We distinguish public profile data, members-only information, restricted project resources and administrative records. Private contact information is not published unless the member chooses to make it public.'], 'marketplace-guidelines' => ['Marketplace Guidelines', 'List only projects and materials you own, represent or have sufficient authority to market. Communicate professionally and perform independent due diligence before concluding any business deal.'], 'copyright-policy' => ['Copyright Policy', 'Copyright concerns may be reported through the platform. Listings may be suspended while credible claims are reviewed.'], 'verification-policy' => ['Verification Policy', 'Association, company, listing and festival verification are distinct. Verification does not guarantee copyright ownership, commercial quality, legal compliance, financial viability or investment performance.']];
        foreach ($pages as $slug => [$title,$content]) {
            Page::updateOrCreate(['slug' => $slug], compact('title', 'content') + ['status' => 'published']);
        }
    }
}
