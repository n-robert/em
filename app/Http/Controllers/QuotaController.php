<?php

namespace App\Http\Controllers;


class QuotaController extends BaseController
{
    /**
     * Get item data.
     *
     * @param int|string $id
     * @return array
     */
    public function getItem($id): array
    {
        $item = parent::getItem($id);
        $details = $item['item']->details ?? [];
        $unused = $item['item']->unused;

        if ($details) {
            foreach ($details as $key => $detail) {
                $details[$key]['unused'] = $unused[$key];
            }
        }

        $item['item']->details = $details;

        return $item;
    }
}
