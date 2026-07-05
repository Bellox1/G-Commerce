<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fournisseurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('nom');
            $table->string('pays')->default('NG'); // Nigeria par défaut
            $table->string('ville')->nullable();
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->string('devise')->default('NGN'); // Naira
            $table->text('notes')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fournisseurs');
    }
};
