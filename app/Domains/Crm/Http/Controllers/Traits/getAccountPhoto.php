<?php
/**
 * @author tmtuan
 * created Date: 07-May-21
 */

namespace App\Domains\Crm\Http\Controllers\Traits;

use App\Domains\Auth\Models\User;
use Intervention\Image\Facades\Image;

trait getAccountPhoto {
    public function getPhotoFromUrl($account, $userId) {
        $userData = User::find($userId);
        if ( !isset($userData->id) ) return false;

        if (filter_var($account['Photo_Url__c'], FILTER_VALIDATE_URL)) {
            $path = $account['Photo_Url__c'];
            $filename = "{$account['Id']}_{$userData->id}";
            $subFolder = storage_path('app/public/users/' . $userData->created_at->format('Y/m'));
            if (!file_exists($subFolder)) {
                mkdir($subFolder, 0755, true);
            }
            $avata = $subFolder . '/' . $filename;

            Image::make($path)->save($avata);
            $updateData = [
                'avata' => $filename
            ];
            User::where('id', $userData->id)
                ->update($updateData);
        }
    }
}
