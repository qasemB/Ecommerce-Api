<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string("title");
            $table->text("descriptions",255)->nullable();
            $table->text("image")->nullable();
            $table->text("logo")->nullable();
            $table->boolean("is_active")->default(true);
            $table->boolean("show_in_menu")->default(true);
            $table->bigInteger("parent_id")->unsigned()->index()->nullable();
            $table->timestamp("deleted_at")->nullable();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('categories')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories');
    }
}
