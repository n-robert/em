<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Address;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $columns = [
            'id',
            'name_ru',
            'description',
            'user_ids',
            'history',
        ];

        $oldData = DB::connection('mysqlx')->table('fmsdocs_addresses')->get();
        Address::truncate();

        foreach ($oldData as $oldDatum) {
            $newData = [];

            foreach ($columns as $column) {
                switch ($column) {
                    case 'name_ru':
                        $key = 'name';
                        break;
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

                if ($column == 'user_ids') {
                    $value = json_encode(array_map(
                        function ($item) {
                            return (int)str_replace(['208', '209', '211', '214', '215'], [2, 3, 2, 4, 5], trim($item));
                        },
                        explode(',', $value)
                    ));
                }

                if ($column == 'history') {
                    $oldValue = json_decode($value);

                    if (!empty($oldValue->date)) {
                        $newValue = [];

                        foreach ($oldValue->date as $k => $date) {
                            $prevValue = [];
                            $tmp = explode(chr(10), $oldValue->prev_value[$k]);
                            array_walk($tmp, function ($item) use (&$prevValue) {
                                $item = explode(': ', $item);

                                if (count($item) == 2) {
                                    list($k, $v) = $item;

                                    if ($k != 'user_ids') {
                                        $prevValue[$k] = $v;
                                    }
                                }
                            });
                            $user = preg_replace('~^#(\d+)\s.+~', '$1', $oldValue->user[$k]);
                            $newValue[] = [
                                'date'       => Carbon::parse($date)->isoFormat('YYYY-MM-DD H:m:s'),
                                'prev_value' => $prevValue,
                                'user'       => $user,
                            ];
                        }

                        $value = json_encode($newValue);
                    }
                }

                $newData[$column] = $value;
            }

            Address::withoutGlobalScopes()->insert($newData);
        }
    }
}
