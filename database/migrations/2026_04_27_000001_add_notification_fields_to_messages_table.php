<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('recipient_user_id')->nullable()->after('id')->constrained('User')->nullOnDelete();
            $table->foreignId('sender_user_id')->nullable()->after('recipient_user_id')->constrained('User')->nullOnDelete();
            $table->string('type', 60)->nullable()->after('content');
            $table->string('title', 120)->nullable()->after('type');
            $table->boolean('is_read')->default(false)->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('recipient_user_id');
            $table->dropConstrainedForeignId('sender_user_id');
            $table->dropColumn(['type', 'title', 'is_read']);
        });
    }
};