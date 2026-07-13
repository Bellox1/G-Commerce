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
        // 1. Add partenaire_id to tenants table
        Schema::table('tenants', function (Blueprint $table) {
            $table->unsignedBigInteger('partenaire_id')->nullable()->after('proprietaire_id');
            // foreign key constraints can be omitted depending on DB support, but let's add it
            $table->foreign('partenaire_id')->references('id')->on('users')->onDelete('set null');
        });

        // 2. Create commission_rules table
        Schema::create('commission_rules', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('code')->unique();
            $table->decimal('prix', 15, 2);
            $table->decimal('commission', 15, 2);
            $table->timestamps();
        });

        // 3. Create commissions table
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('partenaire_id');
            $table->unsignedBigInteger('tenant_id');
            $table->decimal('montant', 15, 2);
            // "en_attente", "reglee"
            $table->string('statut')->default('en_attente');
            $table->timestamps();

            $table->foreign('partenaire_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });

        // 4. Seed default commission rules
        DB::table('commission_rules')->insert([
            [
                'nom' => 'Locale (Licence à vie)',
                'code' => 'locale',
                'prix' => 79900.00,
                'commission' => 3995.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nom' => 'Cloud Sync 1 mois',
                'code' => 'cloud_1',
                'prix' => 3500.00,
                'commission' => 245.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nom' => 'Cloud Sync 3 mois',
                'code' => 'cloud_3',
                'prix' => 9000.00,
                'commission' => 900.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nom' => 'Cloud Sync 6 mois',
                'code' => 'cloud_6',
                'prix' => 16800.00,
                'commission' => 2520.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nom' => 'Cloud Sync 12 mois',
                'code' => 'cloud_12',
                'prix' => 30000.00,
                'commission' => 6000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions');
        Schema::dropIfExists('commission_rules');
    }
};
