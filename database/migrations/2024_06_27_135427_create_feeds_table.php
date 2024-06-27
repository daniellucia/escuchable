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
        Schema::create('feeds', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('url')->unique();
            $table->string('uid')->unique()->default('');
            $table->string('link')->nullable();
            $table->text('description')->nullable();
            $table->string('author')->nullable();
            $table->integer('category_id')->nullable();
            $table->string('language')->nullable();
            $table->string('image')->nullable();
            $table->string('generator')->nullable();
            $table->boolean('visible')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feeds');
    }
};
