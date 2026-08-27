<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('commission_rules', 'primes')) {
            Schema::table('commission_rules', function (Blueprint $table) {
                $table->json('primes')->nullable()->after('commission');
            });
        }

        // Migration des anciennes colonnes fixes (prime_5/10/15) vers le JSON dynamique.
        $paliersParDefaut = [5, 10, 15];
        $colonnes = ['prime_5', 'prime_10', 'prime_15'];

        DB::table('commission_rules')->get()->each(function ($rule) use ($paliersParDefaut, $colonnes) {
            $primes = [];
            foreach ($paliersParDefaut as $i => $seuil) {
                $primes[$seuil] = (float) ($rule->{$colonnes[$i]} ?? 0);
            }
            DB::table('commission_rules')
                ->where('id', $rule->id)
                ->update(['primes' => json_encode($primes)]);
        });

        Schema::table('commission_rules', function (Blueprint $table) use ($colonnes) {
            $existing = array_filter($colonnes, fn ($c) => Schema::hasColumn('commission_rules', $c));
            if ($existing) {
                $table->dropColumn($existing);
            }
        });
    }

    public function down(): void
    {
        Schema::table('commission_rules', function (Blueprint $table) {
            $table->decimal('prime_5', 15, 2)->default(0)->after('commission');
            $table->decimal('prime_10', 15, 2)->default(0)->after('prime_5');
            $table->decimal('prime_15', 15, 2)->default(0)->after('prime_10');
        });

        $paliersParDefaut = [5, 10, 15];
        $colonnes = ['prime_5', 'prime_10', 'prime_15'];

        DB::table('commission_rules')->get()->each(function ($rule) use ($paliersParDefaut, $colonnes) {
            $primes = is_array($rule->primes) ? $rule->primes : json_decode($rule->primes ?? '[]', true);
            $primes = $primes ?: [];
            $values = [];
            foreach ($paliersParDefaut as $i => $seuil) {
                $values[$colonnes[$i]] = (float) ($primes[$seuil] ?? 0);
            }
            DB::table('commission_rules')->where('id', $rule->id)->update($values);
        });

        Schema::table('commission_rules', function (Blueprint $table) {
            $table->dropColumn('primes');
        });
    }
};
