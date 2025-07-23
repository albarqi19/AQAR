<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAppKey
{
    public function handle(Request $request, Closure $next)
    {
        // التحقق من وجود APP_KEY
        $appKey = config('app.key');
        
        if (empty($appKey) || $appKey === 'base64:GENERATED_KEY_WILL_BE_SET_AUTOMATICALLY') {
            // تعيين APP_KEY مباشرة
            $correctKey = 'base64:1jJ7lx/yprzypdaIzSD6nk1GaImlQuKx4QE2+TqQT2Q=';
            
            // تحديث متغيرات البيئة
            $_ENV['APP_KEY'] = $correctKey;
            putenv('APP_KEY=' . $correctKey);
            
            // تعيين قاعدة البيانات SQLite
            $_ENV['DB_CONNECTION'] = 'sqlite';
            $_ENV['DB_DATABASE'] = '/app/database/database.sqlite';
            putenv('DB_CONNECTION=sqlite');
            putenv('DB_DATABASE=/app/database/database.sqlite');
            
            // تحديث config cache
            config(['app.key' => $correctKey]);
            config(['database.default' => 'sqlite']);
            config(['database.connections.sqlite.database' => '/app/database/database.sqlite']);
            
            // إنشاء/تحديث ملف .env
            $envPath = base_path('.env');
            $envContent = file_exists($envPath) ? file_get_contents($envPath) : '';
            
            if (strpos($envContent, 'APP_KEY=') !== false) {
                $envContent = preg_replace('/APP_KEY=.*/', 'APP_KEY=' . $correctKey, $envContent);
            } else {
                $envContent .= "\nAPP_KEY=" . $correctKey . "\n";
            }
            
            if (strpos($envContent, 'DB_CONNECTION=') !== false) {
                $envContent = preg_replace('/DB_CONNECTION=.*/', 'DB_CONNECTION=sqlite', $envContent);
            } else {
                $envContent .= "DB_CONNECTION=sqlite\n";
            }
            
            if (strpos($envContent, 'DB_DATABASE=') !== false) {
                $envContent = preg_replace('/DB_DATABASE=.*/', 'DB_DATABASE=/app/database/database.sqlite', $envContent);
            } else {
                $envContent .= "DB_DATABASE=/app/database/database.sqlite\n";
            }
            
            file_put_contents($envPath, $envContent);
        }
        
        return $next($request);
    }
}
