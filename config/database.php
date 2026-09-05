<?php

use Illuminate\Support\Str;

return [

    'default' => env('DB_CONNECTION', 'sqlite'),

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DB_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),

            /*
            |------------------------------------------------------------------
            | SQLite eşzamanlılık ayarları
            |------------------------------------------------------------------
            | Bu üçü boş bırakılırsa SQLite varsayılana düşer ve varsayılan bu
            | uygulama için yanlış:
            |
            | journal_mode=WAL — varsayılan "delete" modunda her yazma tüm
            |   veritabanını kilitler ve OKUYUCULAR BEKLER. Menü her açıldığında
            |   görüntülenme sayacı yazılıyor (/olcum); yani taramalar
            |   birbirini kilitliyor. WAL'de okuyucu yazıcıyı, yazıcı okuyucuyu
            |   engellemez — bu uygulamadaki tek gerçek darboğazı kaldırır.
            |
            | synchronous=NORMAL — WAL ile birlikte güvenli: işletim sistemi
            |   çökmesinde bile veri bozulmaz, yalnızca son saniyelerin
            |   işlemleri kaybolabilir (kayıp olan şey görüntülenme sayacı).
            |   FULL modun her commit'teki fsync maliyetini ödemeye değmiyor.
            |
            | busy_timeout — kilit bekleyen sorgu hemen hata vermek yerine
            |   beklesin. WAL ile nadiren devreye girer ama emniyet kemeri.
            */
            'busy_timeout' => env('DB_SQLITE_BUSY_TIMEOUT', 5000),
            'journal_mode' => env('DB_SQLITE_JOURNAL', 'WAL'),
            'synchronous' => env('DB_SQLITE_SYNCHRONOUS', 'NORMAL'),
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ],

    ],

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    'redis' => [
        'client' => env('REDIS_CLIENT', 'phpredis'),
        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
        ],
        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],
        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],
    ],

];
