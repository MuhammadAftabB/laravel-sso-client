<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->string('hub_user_id')->nullable();
            $table->string('hub_email')->nullable();
            $table->timestamp('hub_last_synced_at')->nullable();
            $table->boolean('has_role')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
