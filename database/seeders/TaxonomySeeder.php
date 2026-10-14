<?php

namespace Database\Seeders;

use App\Models\CollaborationType;
use App\Models\ContentType;
use App\Models\Genre;
use App\Models\Language;
use App\Models\NewsCategory;
use App\Models\OpportunityType;
use App\Models\ProjectStage;
use App\Models\ProjectTag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TaxonomySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['English' => 'en', 'French' => 'fr', 'Arabic' => 'ar', 'Portuguese' => 'pt', 'Swahili' => 'sw', 'Twi' => 'tw', 'Hausa' => 'ha', 'Yoruba' => 'yo', 'Zulu' => 'zu', 'Wolof' => 'wo'] as $name => $code) {
            Language::firstOrCreate(['name' => $name], ['code' => $code]);
        }foreach (['Drama', 'Comedy', 'Documentary', 'Thriller', 'Romance', 'Action', 'Animation', 'Family', 'Music', 'Historical', 'Crime', 'Fantasy', 'Science Fiction'] as $name) {
            Genre::firstOrCreate(['name' => $name], ['slug' => Str::slug($name)]);
        }foreach (['Feature Film', 'Short Film', 'Documentary', 'TV Series', 'Web Series', 'Animation', 'Music', 'Music Video', 'Podcast', 'Digital Series', 'Documentary Series', 'Television Programme', 'Other Digital Content'] as $name) {
            ContentType::firstOrCreate(['name' => $name], ['slug' => Str::slug($name), 'active' => true]);
        }$i = 0;
        foreach (['Concept', 'Development', 'Pre-production', 'Production', 'Post-production', 'Completed', 'Released', 'Catalogue Title'] as $name) {
            ProjectStage::firstOrCreate(['name' => $name], ['slug' => Str::slug($name), 'sort_order' => ++$i]);
        }foreach (['Funding', 'Co-Producer', 'Distributor', 'Broadcaster', 'Sales Agent', 'Streaming Partner', 'Investor', 'International Sales', 'Production Partner', 'Post-production Partner', 'Licensing', 'Acquisition', 'Sponsorship'] as $name) {
            ProjectTag::firstOrCreate(['name' => $name], ['slug' => Str::slug($name), 'active' => true]);
        }foreach (['Film Grant', 'Festival', 'Lab', 'Residency', 'Market', 'Training', 'Competition', 'Funding Call', 'Workshop'] as $name) {
            OpportunityType::firstOrCreate(['name' => $name], ['slug' => Str::slug($name)]);
        }foreach (['Co-Producer', 'Director', 'Composer', 'Investor', 'Distributor', 'Production Partner', 'Writer', 'Sales Agent'] as $name) {
            CollaborationType::firstOrCreate(['name' => $name], ['slug' => Str::slug($name)]);
        }foreach (['Industry News', 'Association News', 'Member News', 'Film Festivals', 'Funding', 'Distribution', 'Production'] as $name) {
            NewsCategory::firstOrCreate(['name' => $name], ['slug' => Str::slug($name)]);
        }
    }
}
