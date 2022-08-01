<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContentTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('title_label')->default(trans('chronos::interface.Title'));
            $table->boolean('translatable')->default(0);
            $table->text('notes')->nullable();
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
        if (Schema::hasTable('content')) {
            Schema::table('content', function (Blueprint $table) {
                $table->dropForeign('content_type_id_foreign');
            });
        }
        Schema::dropIfExists('content_types');
    }
}
