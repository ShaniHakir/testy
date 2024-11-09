<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the unique constraint on jabber_xmpp
            $table->dropUnique(['jabber_xmpp']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add back the unique constraint if needed to rollback
            $table->unique('jabber_xmpp');
        });
    }
};
