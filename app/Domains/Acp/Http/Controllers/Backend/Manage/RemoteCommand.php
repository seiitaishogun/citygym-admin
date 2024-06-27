<?php
/**
 * @author tmtuan
 * created Date: 12-Apr-21
 */
namespace App\Domains\Acp\Http\Controllers\Backend\Manage;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class RemoteCommand extends Controller {
    public function command() {
        return view('backend.manage.command');
    }

    public function excute(Request $request) {
        $postData = $request->post();

        if ( empty($postData['command']) ) return redirect()->back()->withErrors(__('Please enter Command'));

        switch ($postData['type']) {
            case 'artisan':
                Artisan::call($postData['command']);
                $result = Artisan::output();
                break;
            case 'composer':
                $result = shell_exec($postData['command']);
                break;
        }
        return view('backend.manage.command', ['result' => $result]);

    }
}
