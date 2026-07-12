<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("
            CREATE TABLE users_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                tenant_id INTEGER NULL,
                magasin_id INTEGER NULL,
                name TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                telephone TEXT NULL,
                role TEXT NOT NULL DEFAULT 'vendeur' CHECK(role IN ('super_admin','admin','vendeur','livreur','magasinier','prestataire')),
                roles_secondaires TEXT NULL,
                actif INTEGER NOT NULL DEFAULT 1,
                last_seen DATETIME NULL,
                email_verified_at DATETIME NULL,
                password TEXT NOT NULL,
                remember_token TEXT NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                deleted_at DATETIME NULL,
                FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL,
                FOREIGN KEY (magasin_id) REFERENCES magasins(id) ON DELETE SET NULL
            );

            INSERT INTO users_new (id, tenant_id, magasin_id, name, email, telephone, role, roles_secondaires, actif, last_seen, email_verified_at, password, remember_token, created_at, updated_at, deleted_at)
            SELECT id, tenant_id, magasin_id, name, email, telephone, role, roles_secondaires, actif, last_seen, email_verified_at, password, remember_token, created_at, updated_at, deleted_at FROM users;

            DROP TABLE users;
            ALTER TABLE users_new RENAME TO users;

            CREATE INDEX IF NOT EXISTS users_tenant_id_index ON users(tenant_id);
            CREATE INDEX IF NOT EXISTS users_magasin_id_index ON users(magasin_id);
            CREATE UNIQUE INDEX IF NOT EXISTS users_email_unique ON users(email);
        ");
    }

    public function down(): void
    {
        DB::unprepared("
            CREATE TABLE users_old (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                tenant_id INTEGER NULL,
                magasin_id INTEGER NULL,
                name TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                telephone TEXT NULL,
                role TEXT NOT NULL DEFAULT 'vendeur' CHECK(role IN ('super_admin','admin','vendeur','livreur','magasinier')),
                roles_secondaires TEXT NULL,
                actif INTEGER NOT NULL DEFAULT 1,
                last_seen DATETIME NULL,
                email_verified_at DATETIME NULL,
                password TEXT NOT NULL,
                remember_token TEXT NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                deleted_at DATETIME NULL,
                FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL,
                FOREIGN KEY (magasin_id) REFERENCES magasins(id) ON DELETE SET NULL
            );

            INSERT INTO users_old (id, tenant_id, magasin_id, name, email, telephone, role, roles_secondaires, actif, last_seen, email_verified_at, password, remember_token, created_at, updated_at, deleted_at)
            SELECT id, tenant_id, magasin_id, name, email, telephone, role, roles_secondaires, actif, last_seen, email_verified_at, password, remember_token, created_at, updated_at, deleted_at FROM users WHERE role != 'prestataire';

            DROP TABLE users;
            ALTER TABLE users_old RENAME TO users;

            CREATE INDEX IF NOT EXISTS users_tenant_id_index ON users(tenant_id);
            CREATE INDEX IF NOT EXISTS users_magasin_id_index ON users(magasin_id);
            CREATE UNIQUE INDEX IF NOT EXISTS users_email_unique ON users(email);
        ");
    }
};
