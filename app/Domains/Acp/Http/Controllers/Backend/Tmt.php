<?php
/**
 * Controller tạm, để xử lý table member+user
 * @author tmtuan
 * created Date: 26-Mar-21
 */

namespace App\Domains\Acp\Http\Controllers\Backend;

use App\Domains\Acp\Http\Controllers\Backend\Export\DuplicateUser;
use App\Domains\Auth\Models\Member;
use App\Domains\Auth\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Excel;

class Tmt extends Controller {

    public function moveUserToMember() {
        $old_members = User::join('user_sf_account', 'user_sf_account.user_id', '=', 'users.id')
            ->where('user_sf_account.account_type', 'member')->get();
        echo "<h4>Total: ".count($old_members)."</h4>";
        foreach ($old_members as $memberItem) {
            $member = new Member($memberItem->toArray());
            $member->password = $memberItem->password;
            $member->setCreatedAt($memberItem->created_at);
            $member->setUpdatedAt($memberItem->updated_at);
            $member->save();
            $memberItem->forceDelete();
            echo "<p>convert done  - #{$member->id} - {$member->username} - {$member->email}</p>";
        }
        echo "<h5>Done!</h5>";
    }

    public function exportDuplicateUser() {
        $check = DB::select('SELECT user_id, COUNT(user_id) FROM `user_sf_account` GROUP BY user_id HAVING COUNT(user_id) > 1');
        $fileName = 'duplicate_user.csv';

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
//        return Excel::create($fileName, function($excel) use ($exportData) {
//
//            $excel->sheet('user', function($sheet) use ($exportData) {
//
//                $sheet->fromArray($exportData);
//
//            });
//
//        })->export('csv');

        return \Maatwebsite\Excel\Facades\Excel::download(new DuplicateUser(), 'users.csv', \Maatwebsite\Excel\Excel::CSV);
        dd($exportData);
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('id', 'username', 'name', 'email', 'sf_account_id', 'type');

        $callback = function() use($exportData, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($exportData as $item) {
                $row['id']  = $item->id;
                $row['username']    = $item->username;
                $row['name']    = $item->name;
                $row['email']  = $item->email;
                $row['sf_account_id']  = $item->sf_account_id;
                $row['type']  = $item->account_type;

                fputcsv($file, array($row['id'], $row['username'], $row['name'], $row['email'], $row['sf_account_id'], $row['type']));
            }

            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }
}
