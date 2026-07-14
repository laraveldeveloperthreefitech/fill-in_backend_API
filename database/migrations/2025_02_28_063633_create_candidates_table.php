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
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('profile')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('year_of_experiance')->nullable();
            $table->string('availability_time')->nullable();
            $table->enum('type_of_experiance', ['private', 'public', 'both'])->nullable();
            $table->string('other_qualification')->nullable();
            $table->string('other_software')->nullable();
            $table->string('other_vaccination')->nullable();
            $table->string('address')->nullable();
            $table->string('specialization_name')->nullable();
            $table->string('radius')->nullable();
            $table->string('hourly_rate')->nullable();
            $table->boolean('short_notice')->nullable();
            $table->boolean('permanent_opportunities')->nullable();
            $table->boolean('childrens_check')->nullable();
            $table->boolean('valid_police_check')->nullable();
            $table->boolean('first_aid_certicate')->nullable();
            $table->text('working_in_dentistry')->nullable();
            $table->text('environment_thrive')->nullable();
            $table->text('fun_fact')->nullable();
            $table->string('dob')->nullable();
            $table->string('gender')->nullable();
            $table->string('password');
            $table->boolean('verified')->default(0);
            $table->string('document')->nullable();
            $table->string('otp')->nullable();
            $table->string('longitude')->nullable();
            $table->string('latitude')->nullable();
            $table->dateTime('expire_otp')->nullable();
            $table->string('device_token')->nullable();
            $table->string('social-media-key')->nullable();
            $table->string('timezone')->default('UTC');
            $table->string('language')->nullable();
            $table->string('login_type')->nullable();
            $table->boolean('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
