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
        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('established_year')->nullable();
            $table->text('address')->nullable();
            $table->text('postcode')->nullable();
            $table->string('phone')->nullable();
            $table->string('profile')->nullable();
            $table->unsignedBigInteger('recruiter_id');
             $table->unsignedBigInteger('practice_role_id')->nullable();
            $table->string('practice_size')->nullable();
            $table->string('primarly_looking')->nullable();
            $table->string('working_hours')->nullable();
            $table->string('other_dentistry')->nullable();
            $table->string('other_practice_role')->nullable();
             $table->string('other_use_software')->nullable();
            $table->string('document_name')->nullable();
            $table->string('document')->nullable();
             $table->string('description')->nullable();
              $table->string('web_link')->nullable();
              $table->string('document')->nullable();
            $table->boolean('verification')->default(0);
            $table->boolean('status')->default(1);
            $table->foreign('practice_role_id')->references('id')->on('practice_roles')->onDelete('set null');
            $table->foreign('recruiter_id')->references('id')->on('recruiters')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinics');
    }
};
