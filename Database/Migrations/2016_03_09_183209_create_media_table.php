<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMediaTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('parent_id')
                ->nullable();
            $table->string('hash',32);
            $table->enum('type',['file','dir']);
            $table->string('caption');
            $table->string('ext')->nullable();
            $table->unsignedBigInteger('size')
                ->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('parent_id')
                ->references('id')
                ->on($table->getTable())
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('media');
    }

}
