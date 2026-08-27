<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bc_hotel_attr_type_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('hotel_id');
            $table->unsignedBigInteger('block_type_id');
            $table->text('details')->nullable();
            $table->bigInteger('create_user')->nullable();
            $table->bigInteger('update_user')->nullable();
            $table->timestamps();

            $table->unique(['hotel_id', 'block_type_id'], 'hotel_attr_type_unique');
            $table->index('hotel_id');
            $table->index('block_type_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('bc_hotel_attr_type_details');
    }
};
