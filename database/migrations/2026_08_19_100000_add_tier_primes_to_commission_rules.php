<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('commission_rules', function ($table) {
            $table->decimal('prime_5', 15, 2)->default(0)->after('commission');
            $table->decimal('prime_10', 15, 2)->default(0)->after('prime_5');
            $table->decimal('prime_15', 15, 2)->default(0)->after('prime_10');
        });

        $primes = [
            'essentiel'    => ['prime_5' => 50000,  'prime_10' => 100000, 'prime_15' => 150000],
            'professionnel' => ['prime_5' => 100000, 'prime_10' => 200000, 'prime_15' => 300000],
            'entreprise'   => ['prime_5' => 250000, 'prime_10' => 500000, 'prime_15' => 750000],
        ];

        foreach ($primes as $code => $vals) {
            DB::table('commission_rules')->where('code', $code)->update($vals);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commission_rules', function ($table) {
            $table->dropColumn(['prime_5', 'prime_10', 'prime_15']);
        });
    }
};
