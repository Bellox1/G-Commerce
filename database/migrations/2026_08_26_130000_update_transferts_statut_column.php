<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        try {
            DB::statement("ALTER TABLE transferts MODIFY COLUMN statut VARCHAR(50) NOT NULL DEFAULT 'en_transit'");
        } catch (\Throwable $e) {
            Schema::table('transferts', function (Blueprint $table) {
                $table->string('statut', 50)->default('en_transit')->change();
            });
        }
    }

    public function down(): void
    {
        //
    }
};
