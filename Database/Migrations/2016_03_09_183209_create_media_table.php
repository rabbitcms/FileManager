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

            // These columns are needed for Baum's Nested Set implementation to work.
            // Column names may be changed, but they *must* all exist and be modified
            // in the model.
            $table->unsignedInteger('parent_id')->nullable()->index();
            $table->unsignedInteger('lft')->nullable()->index();
            $table->unsignedInteger('rgt')->nullable()->index();
            $table->unsignedInteger('depth')->nullable();

            $table->string('hash',32);
            $table->enum('type',['file','dir']);
            $table->string('caption');

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
