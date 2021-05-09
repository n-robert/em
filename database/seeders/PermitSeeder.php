<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Permit;

class PermitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $columns = [
            'number',
            'issued_date',
            'expired_date',
            'employer_id',
            'quota_id',
            'details',
            'user_ids',
            'published',
            'history',
        ];

        $oldData = DB::table('robert_fmsdocs_permits')->get();

        foreach ($oldData as $oldDatum) {
            $newData = [];

            foreach ($columns as $column) {
                switch ($column) {
                    case 'created_at':
                        $key = 'created';
                        break;
                    case 'updated_at':
                        $key = 'last_modified';
                        break;
                    default:
                        $key = $column;
                }

                $value = $oldDatum->$key;
                $dateFields = [
                    'issued_date',
                    'expired_date',
                ];

                if (in_array($column, $dateFields)) {
                    $value = $value == '0000-00-00' ? null : $value;
                }

                if ($column == 'user_ids') {
                    $value = str_replace(['208', '209', '214', '215'], ['2', '3', '4', '5'], $value);
                }

                $newData[$column] = $value;
            }

            Permit::insert($newData);
        }
    }
}
