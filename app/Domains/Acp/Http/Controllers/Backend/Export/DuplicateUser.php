<?php
/**
 * @author tmtuan
 * created Date: 12-Apr-21
 */
namespace App\Domains\Acp\Http\Controllers\Backend\Export;

use App\Domains\Auth\Models\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;

class DuplicateUser implements  FromCollection, WithHeadings {
    use Exportable;

    public function headings(): array
    {
        return [
            'id',
            'username',
            'name',
            'email',
            'sf_account_id',
            'type',
        ];
    }

    public function collection()
    {
        $check = DB::select('SELECT user_id, COUNT(user_id) FROM `user_sf_account` GROUP BY user_id HAVING COUNT(user_id) > 1');

        $exportData = [];
        foreach ($check as $item) {
            $user = User::select(['id', 'username', 'name', 'email'])
                ->where('id', $item->user_id)->first();

            if ( !empty($user) ) {
                $sfData = DB::table('user_sf_account')->where('user_id', $user->id)->get();
                foreach ($sfData as $sfItem){
                    if ( isset($sfItem->user_id) ) {
                        $user->sf_account_id = $sfItem->sf_account_id;
                        $user->account_type = $sfItem->account_type;

                        $newUs = $user->toArray();
                        unset($newUs['avatar']);
                        unset($newUs['permissions']);
                        unset($newUs['roles']);
                        $exportData[] = $newUs;
                    }
                }

            }
        }

        return collect($exportData);
    }
}
