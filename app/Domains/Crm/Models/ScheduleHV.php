<?php
/**
 * @author tmtuan
 * created Date: 25-Nov-20
 */

namespace App\Domains\Crm\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ScheduleHV extends Model {
    protected $table = 'salesforce_Schedule_HV__c';
    protected $primaryKey = 'Id';
    protected $casts = ['Id' => 'string'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "Id",
        "OwnerId",
        "IsDeleted",
        "Name",
        "CreatedDate",
        "CreatedById",
        "LastModifiedDate",
        "LastModifiedById",
        "SystemModstamp",
        "Schedule__c",
        "Account__c",
        "Checkin_Time__c",
        "is_Checkin__c",
        "HV_Status__c",
        "Registered_Guest__c",
        "Source__c",
        "is_sync_crm",
        "Is_Guest__c",
        "Start__c"
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public $timestamps = false;

    public function Acccount()
    {
        return $this->hasOne('App\Domains\Crm\Models\SfAcccount','Id','Account__c');
        // return $this->hasMany("App\Domains\Crm\Models\SfAcccount", 'Id','Account__c');
    }

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function Schedule()
    {
        return $this->hasOne('App\Domains\Crm\Models\Schedule','Id','Schedule__c');
        // return $this->hasMany("App\Domains\Crm\Models\Schedule", 'Id','Schedule__c');
    }

    public static function getScheduleById($account_id = null,$option = []){
        if (!$account_id) {
            return [];
        }
        $data = self::join('salesforce_Schedule__c', 'salesforce_Schedule__c.Id', '=', 'salesforce_Schedule_HV__c.Schedule__c')
            ->select(['salesforce_Schedule_HV__c.*', 'salesforce_Schedule__c.Id as schedule', 'salesforce_Schedule__c.Start__c'])
            ->with([
                'Schedule' => function($md) use ($account_id){
                    $md->with([
                        'Contract' => function($md){
                            $md->select(['Id', 'StartDate','EndDate','Status','AccountId']);
                            $md->with([
                                'Benefit' => function($ct){
                                }
                            ]);
                        },
                        'PTAssign' => function($md){
                            $md->select(['Id', 'Name','LastName','FirstName']);
                        },
                        // 'ScheduleTrainer' => function ($md) {
                        // },
                        'Trainer1' => function ($md) {
                            $md->select(['Id', 'Name','LastName','FirstName', 'Nickname__c']);
                        },
                        'Trainer2' => function ($md) {
                            $md->select(['Id', 'Name','LastName','FirstName', 'Nickname__c']);
                        },
                        'Trainer3' => function ($md) {
                            $md->select(['Id', 'Name','LastName','FirstName', 'Nickname__c']);
                        },
                        // 'Studio' => function($md){
                        // },
                        'RecordType' => function($md){
                            $md->select(['Id', 'Name']);
                        }
                    ]);
                    $md->withCount(['ScheduleHV' => function ($md) {
                        $md->where('HV_Status__c', 'Booked');
                    }]);
                    $md->withCount(['ScheduleHV as Registered_Guest__count' => function ($md) use ($account_id) {
                        //$md->select(\DB::raw('sum(Registered_Guest__c)'));
                        $md->where('HV_Status__c', 'Booked');
                        $md->where('Account__c', $account_id);
                        $md->where('Is_Guest__c', 1);
                    }]);
                    $md->withCount(['ScheduleHV as userBooked' => function ($md) use ($account_id) {
                        $md->where('HV_Status__c', 'Booked')
                            ->where('Account__c', $account_id)
                            ->where('Is_Guest__c', 0);
                    }]);

                    //$md->withSum('ScheduleHV', 'Registered_Guest__c');
                    // $md->withCount(['ScheduleHV as requests_1' => function ($query) {
                    //         $query->where('Registered_Guest__c','>=', 1);
                    //     }, 'ScheduleHV as requests_2' => function ($query) {
                    //         $query->where('Registered_Guest__c','<', 1);
                    //     }]);
                },
                // 'Acccount' => function($md){
                //     // $md->select(['Id', 'Name','LastName','FirstName']);
                // },
            ])
            ->whereHas('Schedule', function($q) use ($option){
                if(!empty($option['search'])) {
                    $q->where('Name', 'like', "%".$option['search']."%");
                    $q->orWhere('Club_Name__c', 'like', "%".$option['search']."%");
                    $q->orWhere('Class_Name__c', 'like', "%".$option['search']."%");
                }
                if (!empty($option['club'])) {
                    $q->whereIN('Club__c', explode(',',$option['club']));
                }
                if (!empty($option['class'])) {
                    $q->whereIN('Class__c',explode(',',$option['class']));
                }
                if (!empty($option['time'])) {
                    $q->whereTime('Start__c', '<=', $option['time']);
                    $q->whereTime('End__c', '>=', $option['time']);
                }
                if (!empty($option['date'])) {

                    $q->whereDate('Start__c', $option['date']);
                }else{
                    $now = Carbon::parse(date('Y-m-d').' 00:00:00');
                    $q->where('Start__c', '>=', $now->toDateTimeString());
                }
            })
        ->where('salesforce_Schedule_HV__c.IsDeleted', 0)
        ->where('Account__c',$account_id)
        ->orderBy('HV_Status__c', 'asc')
        ->orderBy('salesforce_Schedule__c.Start__c', 'asc')
        ->paginate($option['per_page'] ?? 15, ['*'], 'page', $option['page']);

        return $data;
    }

    public static function getScheduleById1($account_id = null,$booking_id = null,$page = 1){
        if (!$account_id) {
            return [];
        }
        $limit = 100;

        $selectColumns = [
            'schv.Id booking_id',
            'sc.Name',
            'sc.Capacity__c Capacity__c',
            'sc.Class_Room_Capacity__c Class_Room_Capacity__c',
            'sc.Class__c Class__c',
            'sc.Class_Name__c Class_Name__c',
            'sc.Class_Room_name__c Class_Room_name__c',
            'sc.Club__c Club__c',
            'sc.Club_Name__c Club_Name__c',
            'sc.Contract__c Contract__c',
            'sc.Duration__c Duration__c',
            'sc.End__c End__c',
            'sc.End_Update__c End_Update__c',
            'sc.Start__c Start__c',
            'sc.F_Level__c F_Level__c',
            'sc.Class_Color__c Class_Color__c',
            'sc.Class_Room_name__c Class_Room_name__c',//Studio name

            'ac.Name AS account_name',
        ];
        $sql = 'SELECT ' . implode(',', $selectColumns) . ' ';

        $sql .= 'FROM salesforce_Schedule_HV__c schv ';
        $sql .= 'LEFT JOIN salesforce_Schedule__c sc ON (schv.Schedule__c = sc.Id) ';
        $sql .= 'LEFT JOIN salesforce_Account ac ON (schv.Account__c = ac.Id) ';
        $sql .= 'WHERE schv.Account__c = "'.$account_id.'" ';

        if ($booking_id) {
            $sql .= 'AND schv.id = "'.$booking_id.'" ';
        }

        $sql .= 'ORDER BY sc.Start__c DESC ';
        $sql .= 'LIMIT ' . ((int) $limit * ((int) $page - 1)) . ', ' . (int) $limit;

        $Bookings = DB::select($sql);


        return $Bookings;
    }

    public static function checkin($account_id = null,$id = null){
        if (!$id) {
            return false;
        }
        $booking =  self::where('Id',$id)->where('Account__c',$account_id)->first();
        if (!$booking) {
            return false;
        }

        $now = date("Y-m-d H:i:s");
        $booking->is_Checkin__c = true;
        $booking->Checkin_Time__c = $now;
        $booking->save();

        //log
        activity('api')
            ->causedBy($booking)
            ->withProperties(['ScheduleHV' => $booking])
            ->log('ScheduleHV| checkin');

        return true;
    }

    /**
     * Kiểm tra điều kiện đặt lịch Schedule HV
     ** Điều kiện book:
     *  - KO book trùng giờ
     *  - Có hợp đồng Membership đang active ( status = Activated )
     *  - Nếu product trong hợp đồng là Superior cho phép book trước 48h, các trường hợp khác chỉ được phép booking trước 60min
     * @param array $postData
     * @param Schedule $schedule
     * @return array
     */
    public static function validateBookingScheduleHv(array $postData,Schedule $schedule) {
        $response = [];
        //check booking trùng giờ
        $booking = self::leftJoin('salesforce_Schedule__c', 'salesforce_Schedule__c.Id', '=', 'salesforce_Schedule_HV__c.Schedule__c')
                    ->select(['salesforce_Schedule_HV__c.Id AS booking', 'salesforce_Schedule_HV__c.Name AS booking_name'])
                    ->where('salesforce_Schedule_HV__c.HV_Status__c', 'booked')
                    ->where('salesforce_Schedule_HV__c.Account__c', $postData['Account__c'])
                    ->where('salesforce_Schedule__c.Start__c', $schedule->Start__c)
                    ->get()->first();

        if ( isset($booking->Id) )
        {
            return $response = [
                'status'=> false,
                'message' => 'Lịch bị trùng giờ'
            ];
        }
        else
        {
            DB::enableQueryLog();
            $contract = Contract::where('AccountId', $postData['Account__c'])
                        //->where('Status', 'Activated') //tạm bỏ vi SF chưa sync trạng thái hợp đồng
//                        ->where('Product_Type__c', 'Product MB') //tạm bỏ vi SF chưa sync loại hợp đồng
                        ->whereDate('StartDate', '<=', Carbon::now())
                        ->whereDate('EndDate', '>=', Carbon::now())
                        ->get()->first();

            if ( !isset($contract->Id) ) return $response = [
                'status'=> false,
                'message' => 'Bạn không có hợp đồng MB hoặc hợp đồng của bạn đã hết hạn! Vui lòng liên hệ với lễ tân'
            ];
            else
            {
                return $response = [
                    'status'=> true,
                ];
            }
        }

    }

}
