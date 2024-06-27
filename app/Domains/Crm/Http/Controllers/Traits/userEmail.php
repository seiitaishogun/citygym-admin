<?php
/**
 * @author tmtuan
 * created Date: 29-Dec-20
 */
namespace App\Domains\Crm\Http\Controllers\Traits;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

trait userEmail {
    public function sendPasswordEmail($user) {
        Artisan::call('view:clear');

        $to_name = $user->name;
        $to_email = $user->email;
        $data = [
            'name' => $user->name,
            'account' => $user->username,
            'password' => $user->pw_raw
        ];
        Mail::send('emails.newAccount', $data, function($message) use ($to_name, $to_email) {
            $message->to($to_email, $to_name)->subject('Your New Password Information');
            $message->from('admin@citigym.com.vn', 'System');
        });
    }
}
