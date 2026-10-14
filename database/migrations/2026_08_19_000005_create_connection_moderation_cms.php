<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('view_requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('requester_id')->constrained('users')->restrictOnDelete();
            $t->foreignId('project_id')->constrained()->cascadeOnDelete();
            $t->foreignId('organisation_id')->nullable()->constrained()->nullOnDelete();
            $t->string('position')->nullable();
            $t->string('interest_type')->index();
            $t->string('territory')->nullable();
            $t->text('reason');
            $t->text('message')->nullable();
            $t->string('status')->default('pending')->index();
            $t->text('decision_note')->nullable();
            $t->timestamp('decided_at')->nullable();
            $t->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('expires_at')->nullable();
            $t->timestamps();
            $t->unique(['requester_id', 'project_id']);
        });
        Schema::create('access_grants', function (Blueprint $t) {
            $t->id();
            $t->foreignId('project_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('granted_by')->constrained('users')->restrictOnDelete();
            $t->foreignId('view_request_id')->nullable()->constrained()->nullOnDelete();
            $t->timestamp('granted_at');
            $t->timestamp('expires_at')->nullable();
            $t->timestamp('revoked_at')->nullable();
            $t->timestamps();
            $t->unique(['project_id', 'user_id']);
        });
        Schema::create('conversations', function (Blueprint $t) {
            $t->id();
            $t->string('type');
            $t->nullableMorphs('context');
            $t->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $t->timestamp('last_message_at')->nullable()->index();
            $t->timestamps();
        });
        Schema::create('conversation_participants', function (Blueprint $t) {
            $t->id();
            $t->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->timestamp('last_read_at')->nullable();
            $t->timestamps();
            $t->unique(['conversation_id', 'user_id']);
        });
        Schema::create('messages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $t->foreignId('sender_id')->constrained('users')->restrictOnDelete();
            $t->text('body');
            $t->timestamp('edited_at')->nullable();
            $t->timestamps();
            $t->index(['conversation_id', 'created_at']);
        });
        Schema::create('favourites', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->morphs('favouritable');
            $t->timestamps();
            $t->unique(['user_id', 'favouritable_type', 'favouritable_id'], 'favourites_unique');
        });
        Schema::create('verification_records', function (Blueprint $t) {
            $t->id();
            $t->morphs('verifiable');
            $t->string('type')->index();
            $t->string('status')->default('pending_verification')->index();
            $t->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->text('notes')->nullable();
            $t->timestamp('reviewed_at')->nullable();
            $t->timestamps();
        });
        Schema::create('moderation_actions', function (Blueprint $t) {
            $t->id();
            $t->morphs('moderatable');
            $t->foreignId('actor_id')->constrained('users')->restrictOnDelete();
            $t->string('previous_status')->nullable();
            $t->string('new_status');
            $t->text('comments')->nullable();
            $t->timestamps();
            $t->index(['moderatable_type', 'moderatable_id', 'created_at'], 'moderation_lookup');
        });
        Schema::create('reports', function (Blueprint $t) {
            $t->id();
            $t->foreignId('reporter_id')->constrained('users')->restrictOnDelete();
            $t->morphs('reportable');
            $t->string('reason_category')->index();
            $t->text('description');
            $t->string('status')->default('open')->index();
            $t->text('admin_decision')->nullable();
            $t->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('decided_at')->nullable();
            $t->timestamps();
        });
        Schema::create('audit_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('action')->index();
            $t->nullableMorphs('entity');
            $t->json('metadata')->nullable();
            $t->ipAddress('ip_address')->nullable();
            $t->timestamps();
            $t->index(['entity_type', 'entity_id', 'created_at'], 'audit_entity_lookup');
        });
        Schema::create('news_posts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('author_id')->constrained('users')->restrictOnDelete();
            $t->foreignId('news_category_id')->nullable()->constrained()->nullOnDelete();
            $t->string('title');
            $t->string('slug')->unique();
            $t->string('featured_image_path')->nullable();
            $t->text('excerpt')->nullable();
            $t->longText('content');
            $t->string('seo_title')->nullable();
            $t->text('meta_description')->nullable();
            $t->string('status')->default('draft')->index();
            $t->timestamp('published_at')->nullable()->index();
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('pages', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('slug')->unique();
            $t->longText('content');
            $t->string('seo_title')->nullable();
            $t->text('meta_description')->nullable();
            $t->string('status')->default('published')->index();
            $t->timestamps();
        });
        Schema::create('site_settings', function (Blueprint $t) {
            $t->id();
            $t->string('key')->unique();
            $t->text('value')->nullable();
            $t->string('type')->default('text');
            $t->timestamps();
        });
        Schema::create('contact_submissions', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email');
            $t->string('organisation')->nullable();
            $t->string('subject');
            $t->text('message');
            $t->ipAddress('ip_address')->nullable();
            $t->timestamp('handled_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['contact_submissions', 'site_settings', 'pages', 'news_posts', 'audit_logs', 'reports', 'moderation_actions', 'verification_records', 'favourites', 'messages', 'conversation_participants', 'conversations', 'access_grants', 'view_requests'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
