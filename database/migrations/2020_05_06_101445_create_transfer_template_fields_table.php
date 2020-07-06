<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransferTemplateFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfer_template_fields', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('transfer_template_id');
            $table
                ->foreign('transfer_template_id')
                ->references('id')->on('transfer_templates')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->string('name');
            $table->string('description')->default('');
            $table->jsonb('allowed_mimes')->default('[]');
            $table->unsignedBigInteger('min_count')->default(1);
            $table->unsignedBigInteger('max_count')->default(1);
            $table->unsignedBigInteger('max_size_kb')->default(2048);

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
        Schema::dropIfExists('transfer_template_fields');
    }
}
