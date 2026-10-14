<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $t) {
            $t->id();
            $t->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $t->foreignId('organisation_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('stand_id')->nullable()->constrained()->nullOnDelete();
            $t->string('title');
            $t->string('slug')->unique();
            $t->string('alternative_title')->nullable();
            $t->text('logline')->nullable();
            $t->text('short_synopsis')->nullable();
            $t->text('description')->nullable();
            $t->foreignId('content_type_id')->constrained()->restrictOnDelete();
            $t->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('project_stage_id')->nullable()->constrained()->nullOnDelete();
            $t->unsignedSmallInteger('production_year')->nullable()->index();
            $t->unsignedInteger('runtime_minutes')->nullable();
            $t->unsignedInteger('episode_count')->nullable();
            $t->unsignedInteger('episode_runtime_minutes')->nullable();
            $t->decimal('budget', 16, 2)->nullable();
            $t->string('currency', 3)->nullable();
            $t->string('release_status')->nullable();
            $t->text('rights_available')->nullable();
            $t->text('territories')->nullable();
            $t->text('restricted_territories')->nullable();
            $t->string('poster_path')->nullable();
            $t->string('cover_path')->nullable();
            $t->string('trailer_url')->nullable();
            $t->string('teaser_url')->nullable();
            $t->string('audio_preview_url')->nullable();
            $t->text('screener_url')->nullable();
            $t->string('visibility')->default('public');
            $t->boolean('featured')->default(false)->index();
            $t->string('publication_status')->default('draft')->index();
            $t->timestamp('submitted_at')->nullable();
            $t->timestamp('published_at')->nullable()->index();
            $t->unsignedBigInteger('view_count')->default(0);
            $t->softDeletes();
            $t->timestamps();
            $t->index(['publication_status', 'content_type_id', 'country_id']);
        });
        Schema::create('project_country', function (Blueprint $t) {
            $t->foreignId('project_id')->constrained()->cascadeOnDelete();
            $t->foreignId('country_id')->constrained()->cascadeOnDelete();
            $t->string('relationship')->default('coproduction');
            $t->primary(['project_id', 'country_id']);
        });
        Schema::create('genre_project', function (Blueprint $t) {
            $t->foreignId('project_id')->constrained()->cascadeOnDelete();
            $t->foreignId('genre_id')->constrained()->cascadeOnDelete();
            $t->primary(['genre_id', 'project_id']);
        });
        Schema::create('language_project', function (Blueprint $t) {
            $t->foreignId('project_id')->constrained()->cascadeOnDelete();
            $t->foreignId('language_id')->constrained()->cascadeOnDelete();
            $t->string('kind')->default('original');
            $t->primary(['language_id', 'project_id', 'kind']);
        });
        Schema::create('project_project_tag', function (Blueprint $t) {
            $t->foreignId('project_id')->constrained()->cascadeOnDelete();
            $t->foreignId('project_tag_id')->constrained()->cascadeOnDelete();
            $t->primary(['project_id', 'project_tag_id']);
        });
        Schema::create('project_team_members', function (Blueprint $t) {
            $t->id();
            $t->foreignId('project_id')->constrained()->cascadeOnDelete();
            $t->foreignId('profile_id')->nullable()->constrained()->nullOnDelete();
            $t->string('external_name')->nullable();
            $t->string('role');
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();
        });
        Schema::create('project_media', function (Blueprint $t) {
            $t->id();
            $t->foreignId('project_id')->constrained()->cascadeOnDelete();
            $t->string('type');
            $t->string('path')->nullable();
            $t->string('url')->nullable();
            $t->string('caption')->nullable();
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();
        });
        Schema::create('project_documents', function (Blueprint $t) {
            $t->id();
            $t->foreignId('project_id')->constrained()->cascadeOnDelete();
            $t->string('type');
            $t->string('title');
            $t->string('path');
            $t->string('mime_type');
            $t->unsignedBigInteger('size');
            $t->string('access_level')->default('private')->index();
            $t->timestamps();
        });
        Schema::create('project_rights', function (Blueprint $t) {
            $t->id();
            $t->foreignId('project_id')->constrained()->cascadeOnDelete();
            $t->string('right_type');
            $t->string('territory')->nullable();
            $t->string('availability')->default('available');
            $t->timestamps();
            $t->index(['project_id', 'right_type']);
        });
        Schema::create('festival_recognitions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('project_id')->constrained()->cascadeOnDelete();
            $t->string('festival_name');
            $t->foreignId('festival_country_id')->nullable()->constrained('countries')->nullOnDelete();
            $t->unsignedSmallInteger('year')->nullable();
            $t->string('selection_type')->nullable();
            $t->string('award_category')->nullable();
            $t->string('evidence_url')->nullable();
            $t->string('evidence_path')->nullable();
            $t->string('verification_status')->default('self_declared')->index();
            $t->timestamps();
        });
        Schema::create('copyright_declarations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('project_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->restrictOnDelete();
            $t->text('declaration');
            $t->ipAddress('ip_address')->nullable();
            $t->timestamp('accepted_at');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['copyright_declarations', 'festival_recognitions', 'project_rights', 'project_documents', 'project_media', 'project_team_members', 'project_project_tag', 'language_project', 'genre_project', 'project_country', 'projects'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
