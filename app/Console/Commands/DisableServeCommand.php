<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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
        
        $this->info('🔑 تم تعيين APP_KEY: ' . substr($appKey, 0, 20) . '...');
        
        // إنشاء ملف .env مع APP_KEY
        $envContent = "APP_NAME=\"نظام إدارة العقارات\"\n";
        $envContent .= "APP_ENV=production\n";
        $envContent .= "APP_KEY={$appKey}\n";
        $envContent .= "APP_DEBUG=false\n";
        $envContent .= "APP_LOCALE=ar\n";
        $envContent .= "DB_CONNECTION=sqlite\n";
        $envContent .= "DB_DATABASE=/app/database/database.sqlite\n";
        $envContent .= "LOG_LEVEL=error\n";
        $envContent .= "CACHE_STORE=file\n";
        $envContent .= "SESSION_DRIVER=file\n";
        
        file_put_contents('.env', $envContent);
        $this->info('📄 تم إنشاء ملف .env مع APP_KEY');
        
        // إنشاء قاعدة البيانات
        if (!is_dir('database')) {
            mkdir('database', 0755, true);
        }
        if (!file_exists('database/database.sqlite')) {
            touch('database/database.sqlite');
        }
        $this->info('🗄️ تم إعداد قاعدة البيانات');
        
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
