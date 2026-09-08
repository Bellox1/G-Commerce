<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vente_counters', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('annee')->unique();
            $table->unsignedInteger('counter')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vente_counters');
    }
};