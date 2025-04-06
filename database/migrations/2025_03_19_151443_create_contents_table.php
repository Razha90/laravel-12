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
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', ['task', 'notification', 'material']);
            $table->text('description');
            $table->text('content');
            $table->boolean('visibility')->default(false);
            $table->dateTime('release')->nullable();
            $table->dateTime('deadline')->nullable();
            $table->boolean('canUpload')->default(true);
            $table->boolean('isDeadline')->default(false);
            $table->integer('order')->nullable();
            $table->foreignId('classroom_id')->constrained('classrooms')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
