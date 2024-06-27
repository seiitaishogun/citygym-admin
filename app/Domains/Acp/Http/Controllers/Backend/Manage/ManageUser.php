<?php
/**
 * @author tmtuan
 * created Date: 06-Apr-21
 */
namespace App\Domains\Acp\Http\Controllers\Backend\Manage;

use App\Domains\Auth\Models\User;
use App\Http\Controllers\Controller;

class ManageUser extends Controller {
    public function index() {
        return view('backend.manage.user');
    }

    public function hotResetPass($id) {
        $user = User::find($id);
        if ( empty($user)) return redirect()->back()->withErrors(__('Invalid Request'));

        $user->password_changed_at = now();
        $user->password = '123123';
        $user->force_pass_reset = 0;

        $user->update();

        return redirect()->route('admin.manage.manageUser')->withErrors(__('acp.hot_reset_pw_success', ['value' => '123123']));
    }
}
