<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CategoryToView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_to_view', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('resource_id');
            $table->bigInteger('category_id')->unique();
            $table->string('view_name')->nullable();
            $table->boolean('check')->nullable();
            $table->timestamps();
            $table->index(['category_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('category_to_view');
    }
}
