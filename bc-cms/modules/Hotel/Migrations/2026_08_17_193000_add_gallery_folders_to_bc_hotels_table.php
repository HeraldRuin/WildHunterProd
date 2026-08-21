<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bc_hotels', function (Blueprint $table) {
            $table->text('gallery_folders')->nullable()->after('gallery');
        });
    }

    public function down(): void
    {
        Schema::table('bc_hotels', function (Blueprint $table) {
            $table->dropColumn('gallery_folders');
        });
    }
};
