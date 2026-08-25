<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Для БД, где уже прошла первая миграция блоков.
 * Добавляет вложенные блоки (типы) и переводит связь атрибутов на них.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bc_attr_block_types')) {
            Schema::create('bc_attr_block_types', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name')->nullable();
                $table->unsignedBigInteger('block_id')->nullable()->index();
                $table->string('service', 50)->nullable();
                $table->smallInteger('position')->nullable();
                $table->bigInteger('create_user')->nullable();
                $table->bigInteger('update_user')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('bc_attr_block_types_translations')) {
            Schema::create('bc_attr_block_types_translations', function (Blueprint $table) {
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
            if (!Schema::hasColumn('bc_attrs', 'block_type_id')) {
                $table->unsignedBigInteger('block_type_id')->nullable()->after('name');
            }
        });

        if (Schema::hasColumn('bc_attrs', 'block_id')) {
            Schema::table('bc_attrs', function (Blueprint $table) {
                $table->dropColumn('block_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('bc_attrs', function (Blueprint $table) {
            if (Schema::hasColumn('bc_attrs', 'block_type_id')) {
                $table->dropColumn('block_type_id');
            }
            if (!Schema::hasColumn('bc_attrs', 'block_id')) {
                $table->unsignedBigInteger('block_id')->nullable()->after('name');
            }
        });

        Schema::dropIfExists('bc_attr_block_types_translations');
        Schema::dropIfExists('bc_attr_block_types');
    }
};
