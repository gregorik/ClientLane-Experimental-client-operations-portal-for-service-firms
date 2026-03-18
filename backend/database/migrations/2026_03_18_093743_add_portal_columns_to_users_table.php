<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('firm_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->after('firm_id')->constrained()->nullOnDelete();
            $table->string('title')->nullable()->after('email');
            $table->string('role')->default('staff')->after('title');
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_id');
            $table->dropConstrainedForeignId('firm_id');
            $table->dropColumn(['title', 'role', 'last_login_at']);
        });
    }
};
