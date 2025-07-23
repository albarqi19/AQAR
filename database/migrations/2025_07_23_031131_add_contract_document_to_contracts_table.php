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
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('contract_document_path')->nullable()->after('documents');
            $table->string('contract_document_name')->nullable()->after('contract_document_path');
            $table->string('contract_document_mime_type')->nullable()->after('contract_document_name');
            $table->bigInteger('contract_document_size')->nullable()->after('contract_document_mime_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn([
                'contract_document_path',
                'contract_document_name', 
                'contract_document_mime_type',
                'contract_document_size'
            ]);
        });
    }
};
