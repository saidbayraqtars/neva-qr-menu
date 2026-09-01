<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Aynı alt domain adı için iki kullanıcı aynı anda BEKLEYEN talep açamaz.
 * Kısmi (partial) unique index — yalnız status = pending satırlarını kapsar.
 * SQLite ve PostgreSQL destekler; MySQL'de atlanır (uygulama katmanı korur).
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['sqlite', 'pgsql'], true)) {
            DB::statement(
                'CREATE UNIQUE INDEX IF NOT EXISTS subdomain_requests_pending_unique '.
                'ON subdomain_requests (requested_subdomain) '.
                "WHERE status = 'pending'"
            );
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['sqlite', 'pgsql'], true)) {
            DB::statement('DROP INDEX IF EXISTS subdomain_requests_pending_unique');
        }
    }
};
