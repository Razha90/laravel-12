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
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id(column: 'id');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string(column: 'title');
            $table->string('position');
            $table->text(column: 'description');
            $table->string(column: 'image')->nullable();
            $table->boolean('is_password')->default(value: false);
            $table->string(column: 'password')->nullable();
            $table->string(column: 'code');
            $table->boolean(column: 'status')->default(value: true);
            $table->boolean(column: 'ask_join')->default(value: false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classrooms');
    }
};
