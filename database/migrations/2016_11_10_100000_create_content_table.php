<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('type_id')->unsigned()->index();
            $table->foreign('type_id')->references('id')->on('content_types')->onDelete('cascade');
            $table->string('slug');
            $table->string('title');
            $table->bigInteger('author_id')->unsigned()->index()->default(1);
            $table->foreign('author_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('language')->default(config('app.locale'));
            $table->bigInteger('translation_id')->unsigned()->index()->nullable();
            $table->foreign('translation_id')->references('id')->on('content')->onDelete('cascade');
            $table->bigInteger('parent_id')->unsigned()->index()->nullable();
            $table->foreign('parent_id')->references('id')->on('content')->onDelete('cascade');
            $table->integer('order')->unsigned()->default(0);
            $table->boolean('status')->default(1);
            $table->boolean('lock_delete')->default(0);
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
        Schema::dropIfExists('content');
    }
}
