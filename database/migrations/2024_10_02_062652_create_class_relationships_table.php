<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('class_relationships', function (Blueprint $table) {
            $table->id();
            $table->integer('clase_init_id');
            $table->foreign('clase_init_id')->references('id')->on('clases')
            ->onDelete('cascade'); // Asegúrate de usar cascade;
            $table->integer('clase_finish_id');
            $table->foreign('clase_finish_id')->references('id')->on('clases')
            ->onDelete('cascade'); // Asegúrate de usar cascade;
            $table->integer('type_relationship_id');
            $table->foreign('type_relationship_id')->references('id')->on('type_relationships');
            $table->string('quantity_init')->nullable();
            $table->string('quantity_finish')->nullable();
            $table->string('code_sala');
            $table->integer('tabla_intermedia_identificador')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_relationships');
    }
};
