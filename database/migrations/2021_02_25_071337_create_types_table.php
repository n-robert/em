<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $columns = [];

        $columns['none'] = [
            'string' => [
                '32' => [
                    'user_ids',
                ],
            ],
        ];

        $columns['unique'] = [
            'string' => [
                '32' => [
                    'name_ru',
                ],
            ],
        ];

        Schema::create('types', function (Blueprint $table) use($columns) {
            $table->id();
            add_columns_from_array($columns, $table);
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
        Schema::dropIfExists('types');
    }
}
