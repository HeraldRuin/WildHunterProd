<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bc_hotels', function (Blueprint $table) {
            $table->string('object_type')->nullable()->after('star_rate');
            $table->string('legal_entity')->nullable()->after('object_type');
            $table->string('aggregator_owner_phone')->nullable()->after('legal_entity');
            $table->string('aggregator_email')->nullable()->after('aggregator_owner_phone');
            $table->string('aggregator_telegram')->nullable()->after('aggregator_email');
            $table->string('guest_admin_phone')->nullable()->after('aggregator_telegram');
            $table->string('guest_chat_link')->nullable()->after('guest_admin_phone');
        });
    }

    public function down(): void
    {
        Schema::table('bc_hotels', function (Blueprint $table) {
            $table->dropColumn([
                'object_type',
                'legal_entity',
                'aggregator_owner_phone',
                'aggregator_email',
                'aggregator_telegram',
                'guest_admin_phone',
                'guest_chat_link',
            ]);
        });
    }
};
