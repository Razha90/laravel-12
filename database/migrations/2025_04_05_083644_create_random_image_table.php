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
        Schema::create('random_image', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->text('path')->nullable();
            $table->enum('priority', ['1', '2', '3'])->default('3');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('random_image');
    }
};
