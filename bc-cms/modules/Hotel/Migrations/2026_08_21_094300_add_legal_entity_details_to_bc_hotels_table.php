<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bc_hotels', function (Blueprint $table) {
            $after = Schema::hasColumn('bc_hotels', 'legal_entity')
                ? 'legal_entity'
                : (Schema::hasColumn('bc_hotels', 'object_type') ? 'object_type' : 'star_rate');

            $table->string('legal_inn', 12)->nullable()->after($after);
            $table->string('legal_ogrn', 15)->nullable()->after('legal_inn');
            $table->string('legal_ownership_form')->nullable()->after('legal_ogrn');
            $table->text('legal_requisites')->nullable()->after('legal_ownership_form');
        });
    }

    public function down(): void
    {
        Schema::table('bc_hotels', function (Blueprint $table) {
            $table->dropColumn([
                'legal_inn',
                'legal_ogrn',
                'legal_ownership_form',
                'legal_requisites',
            ]);
        });
    }
};
