<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHiringHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hiring_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id', null);
            $table->date('date', null)->nullable(true);
            $table->unsignedBigInteger('status', null);
            $table->addColumn('int_array', 'user_ids');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hiring_history');
    }
}
