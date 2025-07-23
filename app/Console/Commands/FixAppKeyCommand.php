<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class FixAppKeyCommand extends Command
{
    protected $signature = 'fix:app-key';
    protected $description = 'إصلاح مفتاح التطبيق وإنشاؤه إذا لم يكن موجوداً';

    public function handle()
    {
        $this->info('🔑 بدء إصلاح مفتاح التطبيق...');

        // التحقق من وجود ملف .env
        if (!File::exists(base_path('.env'))) {
            $this->info('📄 إنشاء ملف .env من .env.example...');
            File::copy(base_path('.env.example'), base_path('.env'));
        }

        // توليد مفتاح جديد
        $this->info('🔄 توليد مفتاح جديد...');
        Artisan::call('key:generate', ['--force' => true]);
        
        $this->info('✅ تم إصلاح مفتاح التطبيق بنجاح!');
        
        // تنظيف الكاش
        $this->info('🧹 تنظيف الكاش...');
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        
        $this->info('🌐 التطبيق جاهز للاستخدام!');
        
        return 0;
    }
}
