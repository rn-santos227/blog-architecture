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
        Schema::create('post_lookup', function (Blueprint $table) {
            $table->unsignedBigInteger('post_id')->primary();
            $table->unsignedTinyInteger('shard');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->index(['shard']);
            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_lookup');
    }
};
