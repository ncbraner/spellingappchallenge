<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('banks', function (Blueprint $table) {
            $table->renameColumn('user_id', 'user_uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('banks', function (Blueprint $table) {
            $table->renameColumn('user_uuid', 'user_id');
        });
    }

};
