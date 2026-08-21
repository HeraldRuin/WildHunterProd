<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bc_hotels', function (Blueprint $table) {
            $table->text('gallery_food')->nullable()->after('gallery');
            $table->text('gallery_entertainment')->nullable()->after('gallery_food');
            $table->text('gallery_amenities')->nullable()->after('gallery_entertainment');
        });
    }

    public function down(): void
    {
        Schema::table('bc_hotels', function (Blueprint $table) {
            $table->dropColumn([
                'gallery_food',
                'gallery_entertainment',
                'gallery_amenities',
            ]);
        });
    }
};
