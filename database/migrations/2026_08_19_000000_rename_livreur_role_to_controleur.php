<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Le rôle "livreur" est renommé en "controleur" (plus représentatif :
     * il contrôle/supervise les livraisons). On met à jour l'ENUM, la
     * contrainte CHECK et les données existantes (rôle principal + secondaire).
     */
    private static function tableSql(string $name): ?string
    {
        $row = DB::selectOne("SELECT sql FROM sqlite_master WHERE type='table' AND name='users'");
        return $row && $row->sql ? $row->sql : null;
    }

    private static function rebuildUsers(string $from, string $to): void
    {
        // $from / $to : 'livreur' <-> 'controleur'
        $oldSql  = self::tableSql('users');
        if (!$oldSql) return;

        $newSql   = str_replace($from, $to, $oldSql);
        // Table d'étape sans contrainte CHECK pour accueillir les anciennes valeurs.
        $stageSql = preg_replace('/\s*check\s*\(role in \([^)]*\)\)/i', '', $oldSql);

        $rename = fn ($sql, $new) => preg_replace('/CREATE TABLE\s+"?users"?/i', $new, $sql);

        DB::statement('PRAGMA foreign_keys=OFF');

        DB::statement('DROP TABLE IF EXISTS _old_users');
        DB::statement('DROP TABLE IF EXISTS _stage_users');
        DB::statement('DROP TABLE IF EXISTS _new_users');

        DB::statement('ALTER TABLE users RENAME TO _old_users');
        DB::statement($rename($stageSql, 'CREATE TABLE "_stage_users"'));
        DB::statement('INSERT INTO _stage_users SELECT * FROM _old_users');

        DB::statement("UPDATE _stage_users SET role = '{$to}' WHERE role = '{$from}'");
        DB::statement("UPDATE _stage_users SET roles_secondaires = REPLACE(roles_secondaires, '\"{$from}\"', '\"{$to}\"') WHERE roles_secondaires LIKE '%{$from}%'");

        DB::statement($rename($newSql, 'CREATE TABLE "_new_users"'));
        DB::statement('INSERT INTO _new_users SELECT * FROM _stage_users');

        DB::statement('DROP TABLE _old_users');
        DB::statement('DROP TABLE _stage_users');
        DB::statement('ALTER TABLE _new_users RENAME TO users');

        DB::statement('PRAGMA foreign_keys=ON');
    }

    private static function renameRoles(string $from, string $to): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            // 1. Transformer les données existantes avant de resserrer le schéma.
            DB::statement("UPDATE users SET role = '{$to}' WHERE role = '{$from}'");
            DB::statement("UPDATE users SET roles_secondaires = REPLACE(roles_secondaires, '\"{$from}\"', '\"{$to}\"') WHERE roles_secondaires LIKE '%{$from}%'");

            // 2. Supprimer la contrainte `users_role_check` (implicite ENUM ou
            //    explicite) pour éviter l'erreur 1826 (Duplicate CHECK constraint
            //    name) lors du MODIFY ci-dessous. MySQL recrée automatiquement
            //    cette contrainte pour le nouvel ENUM. Le try/catch couvre le cas
            //    où la contrainte n'existe pas.
            try {
                DB::statement("ALTER TABLE users DROP CONSTRAINT `users_role_check`");
            } catch (\Throwable $e) {
                // contrainte absente : on continue
            }

            // 3. Redéfinir l'ENUM : MySQL recrée automatiquement la contrainte CHECK.
            DB::statement("ALTER TABLE users MODIFY role ENUM('super_admin','admin','vendeur','{$to}','magasinier','prestataire') NOT NULL DEFAULT 'vendeur'");
        } else {
            $oldSql = self::tableSql('users');
            if (!$oldSql) return;
            // Sur base déjà à jour (CHECK contient déjà le nouveau rôle), on se
            // contente de transformer les données sans rebatir la table.
            if (str_contains($oldSql, $to)) {
                DB::statement("UPDATE users SET role = '{$to}' WHERE role = '{$from}'");
                DB::statement("UPDATE users SET roles_secondaires = REPLACE(roles_secondaires, '\"{$from}\"', '\"{$to}\"') WHERE roles_secondaires LIKE '%{$from}%'");
                return;
            }
            self::rebuildUsers($from, $to);
        }
    }

    public function up(): void
    {
        self::renameRoles('livreur', 'controleur');
    }

    public function down(): void
    {
        self::renameRoles('controleur', 'livreur');
    }
};
