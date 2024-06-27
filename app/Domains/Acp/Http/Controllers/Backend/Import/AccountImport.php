<?php
/**
 * @author tmtuan
 * created Date: 04-Jun-21
 */
namespace App\Domains\Acp\Http\Controllers\Backend\Import;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithMappedCells;

class AccountImport implements WithMappedCells, ToModel
{
    public function mapping(): array
    {
        return [
            'Id'  => 'A1',
            'Account_Type__c' => 'B1',
        ];
    }

    public function model(array $row)
    {
        return [
            'Id' => $row['Id'],
            'Account_Type__c' => $row['Account_Type__c'],
        ];
    }
}
