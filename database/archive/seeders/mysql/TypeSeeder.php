<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\Type;

class TypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = [
            'LEGAL_RUS',
            'LEGAL_SEMI',
            'LEGAL_FOREIGN',
            'INDIVIDUAL',
            'PRIVATE',
            'NOTARY',
            'LAWYER',
            'FOREIGN_BRANCH',
            'FOREIGN_REP',
            'UFMS',
            'OUFMS',
            'CLIENT'
        ];

        Type::truncate();

        foreach ($types as $type) {
            Type::insert(['code' => $type, 'user_ids' => '2,3', 'created_at' => Carbon::now()]);
        }
    }
}
