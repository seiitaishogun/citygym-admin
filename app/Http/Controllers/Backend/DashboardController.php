<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

/**
 * Class DashboardController.
 */
class DashboardController extends Controller
{
    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('backend.dashboard');
    }

    public function migrate(Request $request) {
        $qr = $request->all();

        switch ($qr['type']) {
            case 'up':
                \Artisan::call('migrate', array('--path' => 'database/migrations', '--force' => true));
                echo "Running {$qr['type']} command complete!<br>";
                break;
            case 'down':
                $step = $qr['step']??1;
                \Artisan::call('migrate:rollback', array('--path' => 'database/migrations', '--force' => true, '--step' => $step));
                echo "Running {$qr['type']} command complete!<br>";
                break;
        }

        echo '<p>'.date('d/m/Y h:m:s').'</p>';
        exit('Done!');
    }


}
