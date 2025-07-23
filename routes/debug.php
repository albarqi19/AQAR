<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

Route::get('/debug', function () {
    try {
        return response()->json([
            'database' => [
                'connection' => config('database.default'),
                'host' => config('database.connections.pgsql.host'),
                'database' => config('database.connections.pgsql.database'),
                'status' => DB::connection()->getPdo() ? 'متصل' : 'غير متصل'
            ],
            'app' => [
                'name' => config('app.name'),
                'env' => config('app.env'),
                'debug' => config('app.debug'),
                'key' => config('app.key') ? 'موجود' : 'غير موجود',
                'url' => config('app.url'),
                'locale' => config('app.locale')
            ],
            'storage' => [
                'logs_writable' => is_writable(storage_path('logs')),
                'cache_writable' => is_writable(storage_path('framework/cache')),
                'sessions_writable' => is_writable(storage_path('framework/sessions'))
            ],
            'migrations' => [
                'users_table' => Schema::hasTable('users') ? 'موجود' : 'غير موجود',
                'buildings_table' => Schema::hasTable('buildings') ? 'موجود' : 'غير موجود'
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ], 500);
    }
});
