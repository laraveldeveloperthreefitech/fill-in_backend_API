<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Global default search radius (km), configurable from the Admin Panel.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'radius')) {
                $table->string('radius')->nullable()->after('logo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'radius')) {
                $table->dropColumn('radius');
            }
        });
    }
};
