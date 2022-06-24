<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->integer('price');
            $table->float('weight')->nullable();
            $table->bigInteger('brand_id')->nullable()->unsigned()->index();
            $table->text('descriptions')->nullable();
            $table->text('short_descriptions')->nullable();
            $table->text('cart_descriptions')->nullable();
            $table->text('image')->nullable();
            $table->string('alt_image')->nullable();
            $table->text('keywords')->nullable();
            $table->integer('stock')->nullable();
            $table->integer('discount')->nullable();
            $table->timestamp('start_special_at')->nullable();
            $table->timestamp('end_special_at')->nullable();
            $table->boolean('only_us')->default(false);
            $table->boolean('is_active')->default(true);
            $table->bigInteger('view_count')->default(0);
            $table->bigInteger('like_count')->default(0);
            $table->bigInteger('dislike_count')->default(0);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->foreign('brand_id')->references('id')->on('brands')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
