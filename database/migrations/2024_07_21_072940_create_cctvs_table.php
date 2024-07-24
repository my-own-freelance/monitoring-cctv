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
        Schema::create('cctvs', function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->string('name');
            $table->string("url");
            $table->longText('description')->nullable();
            $table->string("image")->nullable();
            $table->unsignedBigInteger("building_id");
            $table->unsignedBigInteger("floor_id");
            $table->foreign("building_id")
                ->references("id")
                ->on("buildings")
                ->onUpdate("cascade");
            $table->foreign("floor_id")
                ->references("id")
                ->on("floors")
                ->onUpdate("cascade");
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cctvs');
    }
};
