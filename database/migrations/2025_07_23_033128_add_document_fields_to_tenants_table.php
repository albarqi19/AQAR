<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // ملفات الهوية
            $table->string('identity_document_path')->nullable()->after('is_active');
            $table->string('identity_document_name')->nullable()->after('identity_document_path');
            
            // ملفات السجل التجاري
            $table->string('commercial_register_path')->nullable()->after('identity_document_name');
            $table->string('commercial_register_name')->nullable()->after('commercial_register_path');
            
            // ملفات إضافية أخرى (يمكن استخدامها لأي نوع آخر)
            $table->string('additional_document1_path')->nullable()->after('commercial_register_name');
            $table->string('additional_document1_name')->nullable()->after('additional_document1_path');
            $table->string('additional_document1_label')->nullable()->after('additional_document1_name');
            
            $table->string('additional_document2_path')->nullable()->after('additional_document1_label');
            $table->string('additional_document2_name')->nullable()->after('additional_document2_path');
            $table->string('additional_document2_label')->nullable()->after('additional_document2_name');
            
            $table->string('additional_document3_path')->nullable()->after('additional_document2_label');
            $table->string('additional_document3_name')->nullable()->after('additional_document3_path');
            $table->string('additional_document3_label')->nullable()->after('additional_document3_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'identity_document_path',
                'identity_document_name',
                'commercial_register_path', 
                'commercial_register_name',
                'additional_document1_path',
                'additional_document1_name',
                'additional_document1_label',
                'additional_document2_path',
                'additional_document2_name',
                'additional_document2_label',
                'additional_document3_path',
                'additional_document3_name',
                'additional_document3_label',
            ]);
        });
    }
};
