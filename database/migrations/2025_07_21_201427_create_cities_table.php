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
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('اسم المدينة');
            $table->string('code')->unique()->nullable()->comment('رمز المدينة');
            $table->text('description')->nullable()->comment('وصف المدينة');
            $table->boolean('is_active')->default(true)->comment('هل المدينة نشطة');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
