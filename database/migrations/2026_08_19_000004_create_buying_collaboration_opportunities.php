<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buying_requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->restrictOnDelete();
            $t->foreignId('organisation_id')->nullable()->constrained()->nullOnDelete();
            $t->string('title');
            $t->string('slug')->unique();
            $t->text('description');
            $t->text('additional_requirements')->nullable();
            $t->string('production_status')->nullable();
            $t->unsignedSmallInteger('minimum_production_year')->nullable();
            $t->string('runtime_requirements')->nullable();
            $t->string('episode_requirements')->nullable();
            $t->text('rights_sought')->nullable();
            $t->text('territories')->nullable();
            $t->date('deadline')->nullable()->index();
            $t->string('publication_status')->default('draft')->index();
            $t->timestamp('published_at')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('buying_request_content_type', function (Blueprint $t) {
            $t->foreignId('buying_request_id')->constrained()->cascadeOnDelete();
            $t->foreignId('content_type_id')->constrained()->cascadeOnDelete();
            $t->primary(['buying_request_id', 'content_type_id']);
        });
        Schema::create('buying_request_genre', function (Blueprint $t) {
            $t->foreignId('buying_request_id')->constrained()->cascadeOnDelete();
            $t->foreignId('genre_id')->constrained()->cascadeOnDelete();
            $t->primary(['buying_request_id', 'genre_id']);
        });
        Schema::create('buying_request_country', function (Blueprint $t) {
            $t->foreignId('buying_request_id')->constrained()->cascadeOnDelete();
            $t->foreignId('country_id')->constrained()->cascadeOnDelete();
            $t->primary(['buying_request_id', 'country_id']);
        });
        Schema::create('buying_request_language', function (Blueprint $t) {
            $t->foreignId('buying_request_id')->constrained()->cascadeOnDelete();
            $t->foreignId('language_id')->constrained()->cascadeOnDelete();
            $t->primary(['buying_request_id', 'language_id']);
        });
        Schema::create('buying_request_responses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('buying_request_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->restrictOnDelete();
            $t->foreignId('project_id')->constrained()->restrictOnDelete();
            $t->text('message')->nullable();
            $t->string('status')->default('submitted')->index();
            $t->timestamps();
            $t->unique(['buying_request_id', 'project_id']);
        });
        Schema::create('collaboration_posts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->restrictOnDelete();
            $t->foreignId('organisation_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('collaboration_type_id')->constrained()->restrictOnDelete();
            $t->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('content_type_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('genre_id')->nullable()->constrained()->nullOnDelete();
            $t->string('title');
            $t->string('slug')->unique();
            $t->text('description');
            $t->date('closing_date')->nullable()->index();
            $t->string('publication_status')->default('draft')->index();
            $t->timestamp('published_at')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('collaboration_post_country', function (Blueprint $t) {
            $t->foreignId('collaboration_post_id')->constrained()->cascadeOnDelete();
            $t->foreignId('country_id')->constrained()->cascadeOnDelete();
            $t->primary(['collaboration_post_id', 'country_id']);
        });
        Schema::create('collaboration_responses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('collaboration_post_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->restrictOnDelete();
            $t->text('message')->nullable();
            $t->timestamps();
            $t->unique(['collaboration_post_id', 'user_id']);
        });
        Schema::create('opportunities', function (Blueprint $t) {
            $t->id();
            $t->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $t->foreignId('opportunity_type_id')->constrained()->restrictOnDelete();
            $t->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $t->string('organisation')->nullable();
            $t->string('title');
            $t->string('slug')->unique();
            $t->text('description');
            $t->text('eligibility')->nullable();
            $t->date('opening_date')->nullable();
            $t->date('deadline')->nullable()->index();
            $t->string('external_application_url')->nullable();
            $t->string('featured_image_path')->nullable();
            $t->decimal('funding_amount', 16, 2)->nullable();
            $t->string('currency', 3)->nullable();
            $t->boolean('featured')->default(false)->index();
            $t->string('publication_status')->default('draft')->index();
            $t->timestamp('published_at')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('opportunity_country', function (Blueprint $t) {
            $t->foreignId('opportunity_id')->constrained()->cascadeOnDelete();
            $t->foreignId('country_id')->constrained()->cascadeOnDelete();
            $t->primary(['opportunity_id', 'country_id']);
        });
    }

    public function down(): void
    {
        foreach (['opportunity_country', 'opportunities', 'collaboration_responses', 'collaboration_post_country', 'collaboration_posts', 'buying_request_responses', 'buying_request_language', 'buying_request_country', 'buying_request_genre', 'buying_request_content_type', 'buying_requests'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
