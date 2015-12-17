<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feeds', function (Blueprint $table) {
            $table->increments('id');
            $table->string("name");
            $table->string("url");
            $table->text("description")->default('');
            $table->string('etag')->default('');
            $table->string('last_modified')->default('');  // TODO: Use a datetime field
            $table->timestamps();

            $table->unique("name");
            $table->unique("url");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('feeds');
    }
}
