<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGuaranteeProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guarantee_product', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('guarantee_id')->unsigned()->index();
            $table->bigInteger('product_id')->unsigned()->index();

            $table->foreign('guarantee_id')->references('id')->on('guarantees')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('guarantee_product');
    }
}
