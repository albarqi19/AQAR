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
