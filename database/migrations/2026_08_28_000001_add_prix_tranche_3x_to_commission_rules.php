<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commission_rules', function (Blueprint $table) {
            $table->decimal('prix_tranche_3x', 15, 2)->nullable()->after('prix');
        });

        // Initialisation : pour essentiel et professionnel, on calcule un défaut arrondi
        // Le super admin pourra ensuite ajuster depuis le panneau admin
        $rules = DB::table('commission_rules')
            ->whereIn('code', ['essentiel', 'professionnel'])
            ->get();

        foreach ($rules as $rule) {
            // Par défaut : prix / 3 arrondi à la centaine supérieure
            $tranche = ceil(($rule->prix / 3) / 100) * 100;
            DB::table('commission_rules')
                ->where('id', $rule->id)
                ->update(['prix_tranche_3x' => $tranche]);
        }
    }

    public function down(): void
    {
        Schema::table('commission_rules', function (Blueprint $table) {
            $table->dropColumn('prix_tranche_3x');
        });
    }
};
