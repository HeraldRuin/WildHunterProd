<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bc_attr_blocks')) {
            Schema::create('bc_attr_blocks', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name')->nullable();
                $table->string('service', 50)->nullable();
                $table->bigInteger('create_user')->nullable();
                $table->bigInteger('update_user')->nullable();
                $table->softDeletes();
                $table->timestamps() ;
            });
        }

        if (!Schema::hasTable('bc_attr_blocks_translations')) {
            Schema::create('bc_attr_blocks_translations', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->bigInteger('origin_id')->nullable();
                $table->string('locale', 10)->nullable();
                $table->string('name')->nullable();
                $table->bigInteger('create_user')->nullable();
                $table->bigInteger('update_user')->nullable();
                $table->unique(['origin_id', 'locale']);
                $table->timestamps();
            });
        }

        Schema::table('bc_attrs', function (Blueprint $table) {
            if (!Schema::hasColumn('bc_attrs', 'block_id')) {
                $table->unsignedBigInteger('block_id')->nullable()->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bc_attrs', function (Blueprint $table) {
            if (Schema::hasColumn('bc_attrs', 'block_id')) {
                $table->dropColumn('block_id');
            }
        });

        Schema::dropIfExists('bc_attr_blocks_translations');
        Schema::dropIfExists('bc_attr_blocks');
    }
};
