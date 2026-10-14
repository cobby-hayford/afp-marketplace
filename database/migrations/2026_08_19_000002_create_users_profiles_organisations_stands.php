<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('first_name');
            $t->string('last_name');
            $t->string('email')->unique();
            $t->timestamp('email_verified_at')->nullable();
            $t->string('password');
            $t->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $t->string('role')->default('member')->index();
            $t->string('account_type')->nullable()->index();
            $t->timestamp('terms_accepted_at')->nullable();
            $t->timestamp('privacy_accepted_at')->nullable();
            $t->timestamp('guidelines_accepted_at')->nullable();
            $t->timestamp('suspended_at')->nullable()->index();
            $t->rememberToken();
            $t->timestamps();
        });
        Schema::create('password_reset_tokens', function (Blueprint $t) {
            $t->string('email')->primary();
            $t->string('token');
            $t->timestamp('created_at')->nullable();
        });
        Schema::create('profiles', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $t->string('slug')->unique();
            $t->string('professional_name')->nullable();
            $t->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $t->string('city')->nullable();
            $t->text('short_bio')->nullable();
            $t->text('biography')->nullable();
            $t->string('photo_path')->nullable();
            $t->string('website')->nullable();
            $t->json('social_links')->nullable();
            $t->text('awards')->nullable();
            $t->string('association_number')->nullable()->index();
            $t->string('verification_status')->default('self_declared')->index();
            $t->string('status')->default('draft')->index();
            $t->boolean('is_public')->default(false)->index();
            $t->string('contact_preference')->default('platform');
            $t->string('phone')->nullable();
            $t->boolean('show_email')->default(false);
            $t->boolean('show_phone')->default(false);
            $t->timestamp('submitted_at')->nullable();
            $t->timestamp('approved_at')->nullable();
            $t->timestamps();
        });
        Schema::create('profile_roles', function (Blueprint $t) {
            $t->id();
            $t->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $t->string('role');
            $t->unique(['profile_id', 'role']);
        });
        Schema::create('profile_genre', function (Blueprint $t) {
            $t->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $t->foreignId('genre_id')->constrained()->cascadeOnDelete();
            $t->primary(['profile_id', 'genre_id']);
        });
        Schema::create('language_profile', function (Blueprint $t) {
            $t->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $t->foreignId('language_id')->constrained()->cascadeOnDelete();
            $t->primary(['language_id', 'profile_id']);
        });
        Schema::create('content_type_profile', function (Blueprint $t) {
            $t->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $t->foreignId('content_type_id')->constrained()->cascadeOnDelete();
            $t->primary(['content_type_id', 'profile_id']);
        });
        Schema::create('organisations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('type')->index();
            $t->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $t->string('city')->nullable();
            $t->text('description')->nullable();
            $t->string('logo_path')->nullable();
            $t->string('cover_path')->nullable();
            $t->string('website')->nullable();
            $t->string('email')->nullable();
            $t->string('phone')->nullable();
            $t->json('social_links')->nullable();
            $t->string('verification_status')->default('self_declared')->index();
            $t->string('approval_status')->default('draft')->index();
            $t->boolean('is_public')->default(false)->index();
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('organisation_members', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organisation_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('role')->default('team_member');
            $t->timestamps();
            $t->unique(['organisation_id', 'user_id']);
        });
        Schema::create('stands', function (Blueprint $t) {
            $t->id();
            $t->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $t->foreignId('profile_id')->nullable()->constrained()->cascadeOnDelete();
            $t->foreignId('organisation_id')->nullable()->constrained()->cascadeOnDelete();
            $t->string('display_name');
            $t->string('slug')->unique();
            $t->string('stand_type')->index();
            $t->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $t->string('logo_path')->nullable();
            $t->string('cover_path')->nullable();
            $t->text('summary')->nullable();
            $t->text('description')->nullable();
            $t->text('offerings')->nullable();
            $t->text('looking_for')->nullable();
            $t->string('website')->nullable();
            $t->json('social_links')->nullable();
            $t->string('verification_status')->default('self_declared')->index();
            $t->boolean('is_public')->default(false)->index();
            $t->boolean('featured')->default(false)->index();
            $t->timestamps();
            $t->index(['country_id', 'stand_type', 'is_public']);
        });
    }

    public function down(): void
    {
        foreach (['stands', 'organisation_members', 'organisations', 'content_type_profile', 'language_profile', 'profile_genre', 'profile_roles', 'profiles', 'password_reset_tokens', 'users'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
