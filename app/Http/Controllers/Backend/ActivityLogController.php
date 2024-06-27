<?php
/**
 * @author tmtuan
 * created Date: 08-Jan-21
 */
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller {
    public function index(Request $request)
    {
        \Artisan::call('view:clear');
        $input = $request->query();
        $per_page = $input['per_page']??100;
        $logData = Activity::orderBy('id', 'DESC')
            ->paginate($per_page);

        return view('backend.log.index', ['logs' => $logData]);
    }

    public function view($id, Request $request) {
        \Artisan::call('view:clear');

        $item = Activity::find($id);
//        echo '<pre>'; print_r($item->properties['benefit']); exit;
        return view('backend.log.detail', ['log' => $item]);
    }
}
