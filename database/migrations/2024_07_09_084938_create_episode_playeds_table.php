<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('episode_playeds', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('episode_id');
            $table->integer('time')->default(0);
            $table->boolean('finished')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('episode_playeds');
    }
};
