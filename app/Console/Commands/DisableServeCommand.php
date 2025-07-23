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
        $this->info('🌐 الرجاء استخدام vendor/bin/heroku-php-apache2');
        
        return 1; // خطأ لمنع التشغيل
    }
}
