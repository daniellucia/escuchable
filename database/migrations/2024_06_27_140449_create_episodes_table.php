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
        Schema::create('episodes', function (Blueprint $table) {
            $table->id();
            $table->integer('feed_id');
            $table->string('title');
            $table->string('slug')->nullable()->unique();
            $table->string('link')->nullable();
            $table->text('description');
            $table->string('image')->nullable();
            $table->string('media_url');
            $table->integer('duration')->default(0);
            $table->dateTime('publication_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('episodes');
    }
};
