<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bc_hotels', function (Blueprint $table) {
            $table->text('how_to_get')->nullable()->after('address');
            $table->string('nearby_city')->nullable()->after('how_to_get');
            $table->string('nearby_city_distance')->nullable()->after('nearby_city');
            $table->string('nearby_airport')->nullable()->after('nearby_city_distance');
            $table->string('nearby_airport_distance')->nullable()->after('nearby_airport');
            $table->string('nearby_station')->nullable()->after('nearby_airport_distance');
            $table->string('nearby_station_distance')->nullable()->after('nearby_station');
        });

        Schema::table('bc_hotel_translations', function (Blueprint $table) {
            $table->text('how_to_get')->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('bc_hotels', function (Blueprint $table) {
            $table->dropColumn([
                'how_to_get',
                'nearby_city',
                'nearby_city_distance',
                'nearby_airport',
                'nearby_airport_distance',
                'nearby_station',
                'nearby_station_distance',
            ]);
        });

        Schema::table('bc_hotel_translations', function (Blueprint $table) {
            $table->dropColumn('how_to_get');
        });
    }
};
