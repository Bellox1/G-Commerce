<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private static function recreateRoleCheck(array $roles): void
    {
        // MySQL 8.0.16+ génère automatiquement une contrainte CHECK implicite
        // nommée `users_role_check` pour les colonnes ENUM. On la supprime
        // d'abord par son nom exact (qu'elle soit implicite ou explicite) afin
        // d'éviter l'erreur 1826 (Duplicate CHECK constraint name), puis on la
        // recrée. Le try/catch couvre le cas où la contrainte n'existe pas.
        try {
            DB::statement("ALTER TABLE users DROP CONSTRAINT `users_role_check`");
        } catch (\Throwable $e) {
            // contrainte absente : on continue
        }

        $list = implode("','", $roles);
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK(role IN ('{$list}'))");
    }

    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        self::recreateRoleCheck(['super_admin', 'admin', 'vendeur', 'controleur', 'magasinier', 'prestataire']);
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        self::recreateRoleCheck(['super_admin', 'admin', 'vendeur', 'controleur', 'magasinier']);
    }
};
