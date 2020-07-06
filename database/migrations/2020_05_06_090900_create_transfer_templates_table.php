<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransferTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfer_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('name');
            $table->string('description')->default('');
            $table->unsignedBigInteger('retention_minutes')->default(60 * 24);
            $table->string('recipient_mail');

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
        Schema::dropIfExists('transfer_templates');
    }
}
