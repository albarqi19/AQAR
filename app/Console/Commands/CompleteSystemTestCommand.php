<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CompleteSystemTestCommand extends Command
{
    protected $signature = 'test:complete-system';
    protected $description = 'اختبار شامل ونظيف للنظام مع إعادة تعيين كاملة';

    public function handle()
    {
        $this->info('🧹 تنظيف قاعدة البيانات وإعادة البناء...');
        
        // إعادة بناء قاعدة البيانات
        $this->call('migrate:fresh');
        
        // إنشاء البيانات الأساسية
        $this->call('db:seed', ['--class' => 'AdminUserSeeder']);
        
        $this->info('🚀 تشغيل اختبار النظام الشامل...');
        
        // تشغيل اختبار النظام
        $this->call('test:system');
        
        return 0;
    }
}
