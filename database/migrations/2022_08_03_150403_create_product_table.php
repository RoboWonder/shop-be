<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopbe_product', function (Blueprint $table) {
            $table->id();
            $table->integer('group_id')->nullable()->default(NULL);
            $table->string('name');
            $table->decimal('base_price')->default(0);
            $table->decimal('price')->default(0);
            $table->enum('status', ['0', '1'])->default('1')->index();
            $table->integer('order')->default(0);
            $table->enum('deleted', ['0', '1'])->default('0');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shopbe_product');
    }
}
