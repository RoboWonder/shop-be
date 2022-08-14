<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableTransaction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopbe_transaction', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['1', '2', '3', '4'])->default('3')->comment('1: Deposit, 2: Withdraw, 3: Buy, 4: Discount');
            $table->integer('user_id');
            $table->integer('order_id')->nullable();
            $table->decimal('amount', 10)->default(0);
            $table->text('description')->nullable();
            $table->enum('deleted', ['0', '1'])->default('0');
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table_transaction');
    }
}
