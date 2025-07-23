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
            
            // تعيين قاعدة البيانات MySQL
            $_ENV['DB_CONNECTION'] = 'mysql';
            $_ENV['DB_HOST'] = $_ENV['MYSQLHOST'] ?? $_ENV['DB_HOST'] ?? 'localhost';
            $_ENV['DB_PORT'] = $_ENV['MYSQLPORT'] ?? $_ENV['DB_PORT'] ?? '3306';
            $_ENV['DB_DATABASE'] = $_ENV['MYSQLDATABASE'] ?? $_ENV['DB_DATABASE'] ?? 'property_management';
            $_ENV['DB_USERNAME'] = $_ENV['MYSQLUSER'] ?? $_ENV['DB_USERNAME'] ?? 'root';
            $_ENV['DB_PASSWORD'] = $_ENV['MYSQLPASSWORD'] ?? $_ENV['DB_PASSWORD'] ?? '';
            
            putenv('DB_CONNECTION=mysql');
            putenv('DB_HOST=' . $_ENV['DB_HOST']);
            putenv('DB_PORT=' . $_ENV['DB_PORT']);
            putenv('DB_DATABASE=' . $_ENV['DB_DATABASE']);
            putenv('DB_USERNAME=' . $_ENV['DB_USERNAME']);
            putenv('DB_PASSWORD=' . $_ENV['DB_PASSWORD']);
            
            // تحديث config cache
            config(['app.key' => $correctKey]);
            config(['database.default' => 'mysql']);
            config(['database.connections.mysql.host' => $_ENV['DB_HOST']]);
            config(['database.connections.mysql.port' => $_ENV['DB_PORT']]);
            config(['database.connections.mysql.database' => $_ENV['DB_DATABASE']]);
            config(['database.connections.mysql.username' => $_ENV['DB_USERNAME']]);
            config(['database.connections.mysql.password' => $_ENV['DB_PASSWORD']]);
            
            // إنشاء/تحديث ملف .env
            $envPath = base_path('.env');
            $envContent = file_exists($envPath) ? file_get_contents($envPath) : '';
            
            if (strpos($envContent, 'APP_KEY=') !== false) {
                $envContent = preg_replace('/APP_KEY=.*/', 'APP_KEY=' . $correctKey, $envContent);
            } else {
                $envContent .= "\nAPP_KEY=" . $correctKey . "\n";
            }
            
            if (strpos($envContent, 'DB_CONNECTION=') !== false) {
                $envContent = preg_replace('/DB_CONNECTION=.*/', 'DB_CONNECTION=mysql', $envContent);
            } else {
                $envContent .= "DB_CONNECTION=mysql\n";
            }
            
            if (strpos($envContent, 'DB_HOST=') !== false) {
                $envContent = preg_replace('/DB_HOST=.*/', 'DB_HOST=' . $_ENV['DB_HOST'], $envContent);
            } else {
                $envContent .= "DB_HOST=" . $_ENV['DB_HOST'] . "\n";
            }
            
            if (strpos($envContent, 'DB_DATABASE=') !== false) {
                $envContent = preg_replace('/DB_DATABASE=.*/', 'DB_DATABASE=' . $_ENV['DB_DATABASE'], $envContent);
            } else {
                $envContent .= "DB_DATABASE=" . $_ENV['DB_DATABASE'] . "\n";
            }
            
            file_put_contents($envPath, $envContent);
        }
        
        return $next($request);
    }
}
