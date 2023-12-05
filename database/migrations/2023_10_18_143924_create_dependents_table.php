<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up()
    {
        Schema::create('dependents', function (Blueprint $table) {
            $table->uuid('uuid')->unique();  // Unique UUID column
            $table->uuid('parent_id');  // Foreign key to the users table's uuid column
            $table->uuid('child_id');   // Foreign key to the users table's uuid column
            $table->foreign('parent_id')->references('uuid')->on('users')->onDelete('cascade');
            $table->foreign('child_id')->references('uuid')->on('users')->onDelete('cascade');
            // If you want timestamps (created_at & updated_at), uncomment the next line:
            // $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('dependents');
    }
};
