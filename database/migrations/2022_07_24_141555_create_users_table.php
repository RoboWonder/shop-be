<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopbe_user', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('username');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('auth_token')->nullable();  // jwt token
            $table->string('login_token')->nullable();  // reset password token
            $table->string('email_token'); // email verify token
            $table->boolean('verified')->default(false);
            $table->string('phone_number');
            $table->enum('role', ['1', '2', '3'])->comment('1: supper-admin, 2: admin, 3: user');
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
        Schema::dropIfExists('shopbe_user');
    }
}
