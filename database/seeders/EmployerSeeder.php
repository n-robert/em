<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Employer;

class EmployerSeeder extends Seeder
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
            'taxpayer_id',
            'user_ids',
            'taxpayer_code',
            'active_business_type',
            'rcoad',
            'bcc',
            'acc_book_number',
            'account_number',
            'bank',
            'ca',
            'bic',
            'acc_reg_number',
            'uni_reg_number',
            'phone',
            'prime_reg_number',
            'history',
            'acc_reg_date',
            'prime_reg_date',
            'uni_reg_date',
        ];

        $changedColumns = [
            'name_ru',
            'full_name_ru',
            'director_id',
            'booker_id',
            'type_id',
            'address_id',
        ];
        $columns = array_merge($columns, $changedColumns);
        $oldData = DB::connection('mysqlx')->table('fmsdocs_employers')->get();
        $types = DB::connection('pgsql')->table('types')->pluck('id', 'code');
        Employer::truncate();

        foreach ($oldData as $oldDatum) {
            $newData = [];

            foreach ($columns as $column) {
                switch ($column) {
                    case 'name_ru':
                        $key = 'name';
                        break;
                    case 'full_name_ru':
                        $key = 'full_name';
                        break;
                    case 'director_id':
                        $key = 'director';
                        break;
                    case 'booker_id':
                        $key = 'booker';
                        break;
                    case 'type_id':
                        $key = 'type';
                        break;
                    case 'address_id':
                        $key = 'address';
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

                $value = str_replace('COM_FMSDOCS_', '', $oldDatum->$key);
                $dateFields = [
                    'acc_reg_date',
                    'prime_reg_date',
                    'uni_reg_date',
                ];

                if (in_array($column, $dateFields)) {
                    $value =
                        in_array($value, ['0000-00-00'], '0000-00-00 00:00:00') ?
                            null :
                            Carbon::parse($value)->isoFormat('YYYY-MM-DD');
                }

                if ($column == 'type_id') {
                    $value = $types[$value];
                }

                if ($column == 'phone') {
                    $value = json_encode(['phone' => $value]);
                }

                if ($column == 'user_ids') {
                    $value = json_encode(array_map(
                        function ($item) {
                            return (int)str_replace(['208', '209', '211', '214', '215'], [2, 3, 2, 4, 5], trim($item));
                        },
                        explode(',', $value)
                    ));
                }

                if (str_ends_with($column, '_id')) {
                    $value = intval($value);
                }

                if ($column == 'history') {
                    $oldValue = json_decode($value);

                    if (!empty($oldValue->date)) {
                        $newValue = [];

                        foreach ($oldValue->date as $k => $date) {
                            $prevValue = [];
                            $tmp = explode(chr(10), $oldValue->prev_value[$k]);
                            array_walk($tmp, function ($item) use (&$prevValue, $dateFields) {
                                $item = explode(': ', $item);

                                if (count($item) == 2) {
                                    list($k, $v) = $item;

                                    if ($k != 'user_ids') {
                                        foreach ($dateFields as $column) {
                                            if (str_starts_with($column, $k)) {
                                                $k = $column;
                                                $v =
                                                    in_array($v, ['0000-00-00'], '0000-00-00 00:00:00') ?
                                                        null :
                                                        Carbon::parse($v)->isoFormat('YYYY-MM-DD');
                                                break;
                                            }
                                        }

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

            Employer::withoutGlobalScopes()->insert($newData);
        }
    }
}
