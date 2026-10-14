<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Opportunity extends Model
{
    use SoftDeletes;

    protected $fillable = ['created_by', 'opportunity_type_id', 'country_id', 'organisation', 'title', 'slug', 'description', 'eligibility', 'opening_date', 'deadline', 'external_application_url', 'featured_image_path', 'funding_amount', 'currency', 'featured', 'publication_status', 'published_at'];

    protected function casts(): array
    {
        return ['opening_date' => 'date', 'deadline' => 'date', 'featured' => 'boolean', 'published_at' => 'datetime', 'funding_amount' => 'decimal:2'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(OpportunityType::class, 'opportunity_type_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function eligibleCountries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class, 'opportunity_country');
    }

    public function scopePublished($q)
    {
        return $q->where('publication_status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now());
    }
}
