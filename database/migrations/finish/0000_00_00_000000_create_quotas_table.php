<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuotasTable extends Migration
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
                    'year',
                ],
            ],
            'unsignedBigInteger' => [
                'employer_id',
            ],
            'jsonb' => [
                'user_ids',    // previous type - int_array
            ],
        ];

        $columns['nullable:true'] = [
            'integer' => [
                'total',
            ],

            'jsonb' => [
                'history',
                'details',
            ],

            'date' => [
                'issued_date',
                'expired_date',
            ],
        ];

        Schema::create('quotas', function (Blueprint $table) use ($columns) {
            $table->id();
            add_columns_from_array($columns, $table);
            $table->index(['year', 'employer_id']);
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
        Schema::dropIfExists('quotas');
    }
}
