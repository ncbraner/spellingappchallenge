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
        Schema::create('userwords', function (Blueprint $table) {
            $table->id(); // Adds an auto-increment primary key named 'id'
            $table->uuid('user_uuid');
            $table->uuid('word_uuid');
            $table->string('list_name');
            $table->boolean('active')->default(true);
            $table->timestamps(); // Adds created_at and updated_at columns

            // Foreign keys
            $table->foreign('user_uuid')->references('uuid')->on('users')->onDelete('cascade');
            $table->foreign('word_uuid')->references('uuid')->on('spelling_words')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('userwords');
    }
};
