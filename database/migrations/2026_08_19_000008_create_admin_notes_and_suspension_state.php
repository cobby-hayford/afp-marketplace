<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('status_before_suspension')->nullable()->after('status');
        });

        Schema::create('admin_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->constrained('users')->restrictOnDelete();
            $table->morphs('notable');
            $table->text('body');
            $table->timestamps();
            $table->index(['notable_type', 'notable_id', 'created_at'], 'admin_notes_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_notes');

        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('status_before_suspension');
        });
    }
};
