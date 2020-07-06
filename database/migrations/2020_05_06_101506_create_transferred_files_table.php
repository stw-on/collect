<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransferFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transferred_files', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('transfer_id');
            $table
                ->foreign('transfer_id')
                ->references('id')->on('transfers')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->uuid('transfer_template_field_id');
            $table
                ->foreign('transfer_template_field_id')
                ->references('id')->on('transfer_template_fields')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->string('filename');
            $table->string('mime');
            $table->unsignedBigInteger('size');

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
        Schema::dropIfExists('transfer_fields');
    }
}
