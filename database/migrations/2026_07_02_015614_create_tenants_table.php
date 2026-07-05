<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('marque')->nullable();
            $table->string('activite')->nullable();
            $table->string('pays')->default('BJ');
            $table->string('ville')->nullable();
            $table->string('telephone')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('logo')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
