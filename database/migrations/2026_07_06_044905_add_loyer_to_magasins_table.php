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
        Schema::table('magasins', function (Blueprint $table) {
            $table->decimal('loyer', 12, 2)->nullable()->after('ville');
        });
    }

    public function down(): void
    {
        Schema::table('magasins', function (Blueprint $table) {
            $table->dropColumn('loyer');
        });
    }
};
