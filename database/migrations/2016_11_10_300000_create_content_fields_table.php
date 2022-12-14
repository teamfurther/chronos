<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContentFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content_fields', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('fieldset_id')->unsigned()->index();
            $table->foreign('fieldset_id')->references('id')->on('content_fieldsets')->onDelete('cascade');
            $table->string('machine_name');
            $table->string('name');
            $table->string('type');
            $table->string('widget');
            $table->string('default')->nullable();
            $table->boolean('repeatable')->default(0);
            $table->text('help_text')->nullable();
            $table->string('rules')->nullable();
            $table->binary('data')->nullable();
            $table->integer('order')->unsigned()->default(0);
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
        Schema::dropIfExists('content_fields');
    }
}
