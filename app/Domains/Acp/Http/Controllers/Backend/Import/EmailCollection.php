<?php
/**
 * @author tmtuan
 * created Date: 11/5/2021
 * project: citygym-admin
 */

namespace App\Domains\Acp\Http\Controllers\Backend\Import;

use Maatwebsite\Excel\Concerns\ToModel;
use App\Domains\Auth\Models\User;

class EmailCollection implements ToModel
{
    public function model(array $row)
    {
        return [
            'email'     => $row[0],
        ];
    }
}
