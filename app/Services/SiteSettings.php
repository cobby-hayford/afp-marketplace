<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SiteSettings
{
    public static function all(): array
    {
        $defaults = [
            'brand_name' => config('app.name', 'African Film Producers'),
            'marketplace_subtitle' => 'Content Marketplace',
            'tagline' => "Africa's Digital Content Trade Hall",
            'platform_disclaimer' => 'African Film Producers provides a discovery, networking and marketplace facilitation service. Verification or publication does not guarantee copyright ownership, commercial quality, legal rights, financial viability or investment performance. Commercial parties remain responsible for due diligence and contractual arrangements.',
            'contact_email' => env('SITE_CONTACT_EMAIL'),
            'contact_phone' => env('SITE_CONTACT_PHONE'),
            'facebook_url' => env('SITE_FACEBOOK_URL'),
            'instagram_url' => env('SITE_INSTAGRAM_URL'),
            'linkedin_url' => env('SITE_LINKEDIN_URL'),
            'x_url' => env('SITE_X_URL'),
        ];

        try {
            if (! Schema::hasTable('site_settings')) {
                return $defaults;
            }
            $stored = Cache::remember('site-settings', 300, fn () => SiteSetting::pluck('value', 'key')->all());

            return array_replace($defaults, $stored);
        } catch (\Throwable) {
            return $defaults;
        }
    }

    public static function forget(): void
    {
        Cache::forget('site-settings');
    }
}
