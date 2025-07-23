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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('user_type', ['admin', 'landlord', 'tenant'])->default('admin')->after('email');
            $table->string('phone')->nullable()->after('user_type');
            $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('set null')->after('phone');
            $table->foreignId('landlord_id')->nullable()->constrained()->onDelete('set null')->after('tenant_id');
            $table->timestamp('last_login_at')->nullable()->after('email_verified_at');
            $table->boolean('is_active')->default(true)->after('last_login_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['user_type', 'phone', 'tenant_id', 'landlord_id', 'last_login_at', 'is_active']);
        });
    }
};
