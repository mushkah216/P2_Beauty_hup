<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            // أعمدة التحقق بالإيميل — نفس نمط الخبير
            $table->string('otp_code', 6)->nullable()->after('password');
            $table->timestamp('otp_expires_at')->nullable()->after('otp_code');
            $table->timestamp('email_verified_at')->nullable()->after('otp_expires_at');

            // حالة الحساب — تلات حالات لأن السوبر أدمن لازم يوافق ع التسجيل
            $table->enum('account_status', ['pending', 'active', 'suspended'])
                  ->default('pending')
                  ->after('is_active');

            $table->boolean('is_banned')->default(false)->after('account_status');
            $table->timestamp('last_login_at')->nullable()->after('is_banned');
        });
    }

    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn([
                'otp_code',
                'otp_expires_at',
                'email_verified_at',
                'account_status',
                'is_banned',
                'last_login_at',
            ]);
        });
    }
};