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
        if (Schema::hasTable('chats')) {
            return;
        }

        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('canditidate_id')->nullable();
            $table->unsignedBigInteger('recruiter_id')->nullable();
            $table->enum('sendBy',['candidate','recruiter']);
            $table->text('message');
            $table->boolean('status')->default(0);
            $table->timestamps();
            $table->foreign('canditidate_id')->references('id')->on('candidates')->onDelete('set null');
            $table->foreign('recruiter_id')->references('id')->on('recruiters')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
