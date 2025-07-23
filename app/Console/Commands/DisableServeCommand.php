<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DisableServeCommand extends Command
{
    protected $signature = 'serve {--host=127.0.0.1} {--port=8000} {--tries=} {--no-reload}';
    protected $description = 'تم تعطيل أمر serve - استخدم Apache بدلاً من ذلك';

    public function handle()
    {
        $this->error('🚫 تم تعطيل أمر artisan serve');
        $this->info('✅ يتم استخدام Apache للخادم');
        $this->info('🌐 تشغيل Apache...');
        
        // تشغيل Apache مباشرة
        $port = $_ENV['PORT'] ?? 8000;
        $command = "vendor/bin/heroku-php-apache2 -p {$port} public/";
        
        $this->info("🚀 تشغيل: {$command}");
        
        // استبدال العملية الحالية بـ Apache
        exec($command);
        
        return 0;
    }
}
