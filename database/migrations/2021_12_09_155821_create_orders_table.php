<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->index();
            $table->bigInteger('cart_id')->unsigned()->index();
            $table->bigInteger('discount_id')->unsigned()->index()->nullable();
            $table->bigInteger('delivery_id')->unsigned()->index();
            $table->bigInteger('status_id')->unsigned()->index()->nullable();
            $table->string('user_fullname');
            $table->string('amount');
            $table->string('discount_price')->nullable();
            $table->text('address');
            $table->string('postal_code')->nullable();
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('pay_method')->nullable();
            $table->string('pay_amount')->nullable();
            $table->timestamp('pay_at')->nullable();
            $table->string('pay_card_number')->nullable();
            $table->string('pay_bank')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('cart_id')->references('id')->on('carts');
            $table->foreign('discount_id')->references('id')->on('discounts');
            $table->foreign('delivery_id')->references('id')->on('deliveries');
            $table->foreign('status_id')->references('id')->on('statuses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
