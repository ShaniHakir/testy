<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // This migration is no longer needed as we've created a new one
        // that adds both quantity and price
    }

    public function down(): void
    {
        // No action needed
    }
};