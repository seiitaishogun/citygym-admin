<?php

namespace App\Console\Commands;

use App\Domains\Crm\Models\ScheduleHV;
use App\Domains\TmtSfObject\Classes\SObject;
use App\Models\JobLog;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncScheduleHv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:schedule-hv';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync the ScheduleHv data to SF CRM';

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
        $scheduleHvData = ScheduleHV::where('is_sync_crm', 0)->get();
        if ( count($scheduleHvData) > 0 ) {
            $content = [];
            foreach ($scheduleHvData as $item) {
                //push data to SF CRM
                $scheduleData = [
                    'Account__c' => $item->Account__c,
                    'Schedule__c' => $item->Schedule__c,
                    'Registered_Guest__c' => $item->Registered_Guest__c,
                    'Source__c' => $item->Source__c,
                    'HV_Status__c' => $item->HV_Status__c,
                ];
                try {
                    $content['send'][$item->Id] = $scheduleData;
                    $response = SObject::create('Schedule_HV__c', $scheduleData);
                    if (isset($response->status_code) && in_array($response->status_code, [400, 404])) {
                        $content['response'][$item->Id] = [
                            'status_code' => $response->status_code,
                            'message' => $response->message,
                            'data' => $scheduleData
                        ];
                    } else {
                        $content['response'][$item->Id] = $response;
                        $item->is_sync_crm = 1;
                        $timezone = config('app.timezone');
                        $item->Id = $response;
                        $item->LastModifiedDate = Carbon::now($timezone);
                        $item->sync_result = $response->status_code;
                        if ($response->status_code == 200)
                            $item->last_sync_success = date('Y-m-d H:i:s');
                        $item->save();
                    }

                } catch (\Exception $e) {
                    return response()->json($e->getMessage(), 422);
                }
            }
            $end = microtime(true);
            $duration = $end - $start;
            $log = [
                'job_title' => 'sync_schedule_hv_to_crm',
                'status' => 'done',
                'duration' => $duration,
                'response' => json_encode($content)
            ];
            JobLog::create($log);
        }
        $this->info('Sync Schedule HV Done');
    }
}
