<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SwitchDatabase
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Ambil nama database dari user
            $databaseName = $user->database;

            if ($databaseName) {
                // Konfigurasi koneksi database
                Config::set('database.connections.tenant.database', $databaseName);

                // Set koneksi default ke tenant
                DB::purge('tenant'); // Reset koneksi sebelumnya
                DB::setDefaultConnection('tenant');
            }
        }

        return $next($request);
    }
}
