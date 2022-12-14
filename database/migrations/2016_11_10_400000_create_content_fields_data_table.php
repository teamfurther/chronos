<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContentFieldsDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content_fields_data', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('content_id')->unsigned()->index();
            $table->foreign('content_id')->references('id')->on('content')->onDelete('cascade');
            $table->integer('fieldset_repetition_key')->unsigned()->default(0);
            $table->bigInteger('field_id')->unsigned()->index();
            $table->foreign('field_id')->references('id')->on('content_fields')->onDelete('cascade');
            $table->integer('field_repetition_key')->unsigned()->default(0);
            $table->longText('value')->nullable();
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
        Schema::dropIfExists('content_fields_data');
    }
}
