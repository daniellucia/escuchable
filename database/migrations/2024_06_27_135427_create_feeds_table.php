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
            $table->string('slug')->nullable()->unique();
            $table->string('url')->unique();
            $table->string('link')->nullable();
            $table->string('copyright')->nullable();
            $table->text('description')->nullable();
            $table->integer('category_id')->default(0);
            $table->integer('author_id')->default(0);
            $table->string('language')->nullable();
            $table->string('image')->nullable();
            $table->string('generator')->nullable();
            $table->boolean('visible')->nullable();
            $table->integer('count')->default(0);
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
