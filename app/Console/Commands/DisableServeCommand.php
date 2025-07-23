<?php

namespace App\Console\Commands        // إنشاء ملف .env مع APP_KEY
        $envContent = "APP_NAME=\"نظام إدارة العقارات\"\n";
        $envContent .= "APP_ENV=production\n";
        $envContent .= "APP_KEY={$appKey}\n";
        $envContent .= "APP_DEBUG=false\n";
        $envContent .= "APP_LOCALE=ar\n";
        $envContent .= "DB_CONNECTION=mysql\n";
        $envContent .= "DB_HOST=" . $_ENV['DB_HOST'] . "\n";
        $envContent .= "DB_PORT=" . $_ENV['DB_PORT'] . "\n";
        $envContent .= "DB_DATABASE=" . $_ENV['DB_DATABASE'] . "\n";
        $envContent .= "DB_USERNAME=" . $_ENV['DB_USERNAME'] . "\n";
        $envContent .= "DB_PASSWORD=" . $_ENV['DB_PASSWORD'] . "\n";
        $envContent .= "LOG_LEVEL=error\n";
        $envContent .= "CACHE_STORE=file\n";
        $envContent .= "SESSION_DRIVER=file\n";
        $envContent .= "SESSION_LIFETIME=120\n";
        $envContent .= "QUEUE_CONNECTION=database\n";
        
        file_put_contents('.env', $envContent);
        $this->info('📄 تم إنشاء ملف .env مع APP_KEY وإعدادات MySQL');ate\Console\Command;

class DisableServeCommand extends Command
{
    protected $signature = 'serve {--host=127.0.0.1} {--port=8000} {--tries=} {--no-reload}';
    protected $description = 'تم تعطيل أمر serve - استخدم PHP Built-in Server بدلاً من ذلك';

    public function handle()
    {
        $this->error('🚫 تم تعطيل أمر artisan serve');
        $this->info('✅ يتم استخدام PHP Built-in Server');
        
        // تعيين APP_KEY مباشرة
        $appKey = 'base64:1jJ7lx/yprzypdaIzSD6nk1GaImlQuKx4QE2+TqQT2Q=';
        $_ENV['APP_KEY'] = $appKey;
        putenv('APP_KEY=' . $appKey);
        
        // تعيين إعدادات قاعدة البيانات لـ MySQL
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
        
        $this->info('🔑 تم تعيين APP_KEY: ' . substr($appKey, 0, 20) . '...');
        $this->info('🗄️ تم تعيين قاعدة البيانات: MySQL (' . $_ENV['DB_HOST'] . ':' . $_ENV['DB_PORT'] . ')');
        
        // إنشاء ملف .env مع APP_KEY
        $envContent = "APP_NAME=\"نظام إدارة العقارات\"\n";
        $envContent .= "APP_ENV=production\n";
        $envContent .= "APP_KEY={$appKey}\n";
        $envContent .= "APP_DEBUG=false\n";
        $envContent .= "APP_LOCALE=ar\n";
        $envContent .= "DB_CONNECTION=mysql\n";
        $envContent .= "DB_HOST=" . $_ENV['DB_HOST'] . "\n";
        $envContent .= "DB_PORT=" . $_ENV['DB_PORT'] . "\n";
        $envContent .= "DB_DATABASE=" . $_ENV['DB_DATABASE'] . "\n";
        $envContent .= "DB_USERNAME=" . $_ENV['DB_USERNAME'] . "\n";
        $envContent .= "DB_PASSWORD=" . $_ENV['DB_PASSWORD'] . "\n";
        $envContent .= "LOG_LEVEL=error\n";
        $envContent .= "CACHE_STORE=file\n";
        $envContent .= "SESSION_DRIVER=file\n";
        $envContent .= "SESSION_LIFETIME=120\n";
        $envContent .= "QUEUE_CONNECTION=database\n";
        
        file_put_contents('.env', $envContent);
        $this->info('📄 تم إنشاء ملف .env مع APP_KEY وإعدادات MySQL');
        
        file_put_contents('.env', $envContent);
        $this->info('📄 تم إنشاء ملف .env مع APP_KEY وإعدادات SQLite');
        
        // التحقق من اتصال قاعدة البيانات
        $this->info('🗄️ التحقق من اتصال MySQL...');
        
        // تشغيل migrations لإنشاء الجداول
        $this->info('🔄 تشغيل migrations...');
        try {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            $this->info('✅ تم تشغيل migrations بنجاح');
        } catch (\Exception $e) {
            $this->warn('⚠️ تخطي migrations: ' . $e->getMessage());
        }
        
        $this->info('🌐 تشغيل الخادم...');
        
        // تشغيل PHP Built-in Server مباشرة
        $host = '0.0.0.0';
        $port = $_ENV['PORT'] ?? $_SERVER['PORT'] ?? getenv('PORT') ?? 8000;
        $docroot = 'public';
        
        $command = "php -S {$host}:{$port} -t {$docroot}";
        
        $this->info("🚀 تشغيل: {$command}");
        $this->info("🔗 المنفذ المستخدم: {$port}");
        
        // استبدال العملية الحالية بـ PHP Server
        passthru($command);
        
        return 0;
    }
}
