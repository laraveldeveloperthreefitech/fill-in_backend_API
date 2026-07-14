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
        Schema::create('job_listings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedBigInteger('clinic_id');
            $table->string('salary_range_from')->nullable();
            $table->unsignedBigInteger('specialization_id')->nullable();
            $table->foreign('specialization_id')->references('id')->on('specializations')->onDelete('set null');
            $table->string('vacancy')->nullable();
            $table->string('shift')->nullable();
            $table->string('city')->nullable();
            $table->string('salary_range_to')->nullable();
            $table->string('experiance_level')->nullable();
            $table->text('job_description')->nullable();
            $table->string('require_detail')->nullable();
            $table->text('benefits')->nullable();
            $table->date('expire_date')->nullable();
            $table->text('require_document')->nullable();
            $table->string('address')->nullable();
            $table->string('short_address')->nullable();
            $table->boolean('status')->default(1);
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_listings');
    }
};
