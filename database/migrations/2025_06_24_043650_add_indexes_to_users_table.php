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
            // Add indexes for better query performance
            $table->index('role', 'users_role_index');
            $table->index('is_active', 'users_is_active_index');
            $table->index(['role', 'is_active'], 'users_role_active_index');
            $table->index('created_at', 'users_created_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the indexes
            $table->dropIndex('users_role_index');
            $table->dropIndex('users_is_active_index');
            $table->dropIndex('users_role_active_index');
            $table->dropIndex('users_created_at_index');
        });
    }
};
