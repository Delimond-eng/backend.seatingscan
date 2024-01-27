<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('evenements', function (Blueprint $table) {
            $table->id();
            $table->string('event_nom');
            $table->string('event_code')->unique();
            $table->string('event_couple_nom');
            $table->string('event_status')->default('actif');
            $table->timestamp('event_date_heure')->nullable();
            $table->timestamp('event_created_At')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('evenements');
    }
};
