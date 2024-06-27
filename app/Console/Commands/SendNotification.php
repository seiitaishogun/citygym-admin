<?php

namespace App\Console\Commands;

use App\Domains\Acp\Traits\PushNotification;
use App\Domains\Crm\Models\AppNotification;
use App\Models\JobLog;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendNotification extends Command
{
    use PushNotification;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'push:notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Notification to user by Firebase';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $start = microtime(true);
        $_noti = new AppNotification;

        $notis = $_noti::where('is_sent', 0)
                ->where('send_time', '<=', Carbon::now())
                ->get();
        if ( count( $notis ) > 0 ) {
            foreach ($notis as $item) {
                if ( $item->name == $_noti::SALE_APP ) $appName = 'sale';
                else if ( $item->name == $_noti::MB_APP ) $appName = 'member';

                if ( empty($item->group) ) {
                    if ( $item->user_id > 0 ) $group = 'user_'.$item->user_id;
                    else $group = 'all';
                } else $group = $item->group;


                $data = [
                    'group' => $group,
                    'app' => $appName,
                ];
                if ( !empty($item->data_option) ) $data['data'] = json_decode($item->data_option, true);

                $content['send'][$item->id] = array_merge($data, $item->toArray());
                // Push notification
                $result = $this->pushMessage($item->title, $item->content, $data);
                if ( $result['success'] ) {
                    $item->is_sent = 1;
                    $item->save();

                    $content['response'][$item->id] = $result;
                }
                else $content['response'][$item->id] = $result;
            }
            $end = microtime(true);
            $duration = $end - $start;
            $log = [
                'job_title' => 'push_notification',
                'status' => 'done',
                'duration' => $duration,
                'response' => json_encode($content)
            ];
            JobLog::create($log);
        }
    }
}
