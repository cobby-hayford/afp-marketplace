<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->string('iso_code', 3)->unique();
            $t->string('slug')->unique();
            $t->string('region')->nullable()->index();
            $t->boolean('is_african')->default(false)->index();
            $t->timestamps();
        });
        Schema::create('languages', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->string('code', 12)->nullable()->unique();
            $t->timestamps();
        });
        Schema::create('genres', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->string('slug')->unique();
            $t->timestamps();
        });
        Schema::create('content_types', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->string('slug')->unique();
            $t->boolean('active')->default(true)->index();
            $t->timestamps();
        });
        Schema::create('project_stages', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->string('slug')->unique();
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();
        });
        Schema::create('project_tags', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->string('slug')->unique();
            $t->boolean('active')->default(true);
            $t->timestamps();
        });
        Schema::create('opportunity_types', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->string('slug')->unique();
            $t->timestamps();
        });
        Schema::create('collaboration_types', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->string('slug')->unique();
            $t->timestamps();
        });
        Schema::create('news_categories', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->string('slug')->unique();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['news_categories', 'collaboration_types', 'opportunity_types', 'project_tags', 'project_stages', 'content_types', 'genres', 'languages', 'countries'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
