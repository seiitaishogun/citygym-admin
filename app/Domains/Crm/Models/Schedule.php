<?php
/**
 * @author tmtuan
 * created Date: 25-Nov-20
 */

namespace App\Domains\Crm\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Schedule extends Model
{
    protected $table = 'salesforce_Schedule__c';
    protected $primaryKey = 'Id';
    protected $casts = [
        'Id' => 'string',
        'PT_Check_In__c' => 'boolean',
        'MS_Check_In__c' => 'boolean',
        'Member_Check_In__c' => 'boolean',
        'Start__c' => 'datetime'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Id',
        'OwnerId',
        'IsDeleted',
        'Name',
        'RecordTypeId',
        'CreatedDate',
        'CreatedById',
        'LastModifiedDate',
        'LastModifiedById',
        'SystemModstamp',
        'LastViewedDate',
        'LastReferencedDate',
        'Studio__c',
        'Class__c',
        'Capacity__c',
        'Duration__c',
        'Opportunity_F__c',
        'Start__c',
        'End__c',
        'Account_Name__c',
        'PT_Assign__c',
        'Club_PT_Session__c',
        'Class_Group__c',
        'Class_Name__c',
        'Class_Room_name__c',
        'List_Trainer__c',
        'Class_Color__c',
        'Class_Room_Capacity__c',
        'Club__c',
        'Trainer_1__c',
        'Trainer_2__c',
        'Trainer_3__c',
        'PT_Check_In__c',
        'MS_Check_In__c',
        'Member_Check_In__c',
        'Club_Name__c',
        'Status__c',
        'Card__c',
        'Contract__c',
        'Opportunity_T__c',
        'End_Update__c',
        'F_Level__c',
        'Description__c',
        'Cancel_Time__c',
        'Checkin_time__c',
        'Close_checkin__c',
        'Studio_Name__c',
        'Booking_Time_Start__c',
        'Booking_Time_End__c',
        'Check_In_Time_Start__c',
        'Check_In_Time_End__c',
        'Cancel_Time_End__c',
        'Guest_Booking_Time_Start__c',
        'Guest_Booking_Time_End__c',
        'Contract_Code__c'
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];


    public $timestamps = false;

    protected $dates = ['Start__c', 'End__c', 'CreatedDate', 'Booking_Time_Start__c', 'Booking_Time_End__c',
        'Check_In_Time_Start__c', 'Check_In_Time_End__c', 'Cancel_Time_End__c', 'Guest_Booking_Time_Start__c', 'Guest_Booking_Time_End__c'];


    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function Accounts()
    {
        return $this->belongsToMany('App\Domains\Crm\Models\SfAcccount', 'salesforce_Schedule_HV__c', 'Account__c', 'Schedule__c');
    }

    public function ScheduleHV()
    {
        return $this->hasMany('App\Domains\Crm\Models\ScheduleHV', 'Schedule__c', 'Id');
    }


    public function Studio()
    {
        return $this->hasOne('App\Domains\Crm\Models\Studio', 'Id', 'Studio__c');
    }

    // public function ScheduleHV()
    // {
    //     return $this->hasMany("App\Domains\Crm\Models\ScheduleHV",'Schedule__c', 'Id');
    // }

    public function RecordType()
    {
        return $this->hasOne('App\Domains\Crm\Models\RecordType', 'Id', 'RecordTypeId');
    }


    public function OpportunityT()
    {
        return $this->hasOne('App\Domains\Crm\Models\Opportunity', 'Id', 'Opportunity_T__c');
    }

    public function OpportunityF()
    {
        return $this->hasOne('App\Domains\Crm\Models\Opportunity', 'Id', 'Opportunity_F__c');
    }

    public function Contract()
    {
        return $this->hasOne('App\Domains\Crm\Models\Contract', 'Id', 'Contract__c');
    }

    public function Club()
    {
        return $this->hasOne('App\Domains\Crm\Models\Club', 'Id', 'Club__c');
    }

    public function ClubPTSession()
    {
        return $this->hasOne('App\Domains\Crm\Models\Club', 'Id', 'Club_PT_Session__c');
    }

    public function Class()
    {
        return $this->hasOne('App\Domains\Crm\Models\SfClass', 'Id', 'Class__c');
    }

    public function AccountName()
    {
        return $this->hasOne('App\Domains\Crm\Models\SfAcccount', 'Id', 'Account_Name__c');
    }

    public function PtAssign()
    {
        return $this->hasOne('App\Domains\Crm\Models\SfAcccount', 'Id', 'PT_Assign__c');
    }

    public function Trainer1()
    {
        return $this->hasOne('App\Domains\Crm\Models\SfAcccount', 'Id', 'Trainer_1__c');
    }

    public function Trainer2()
    {
        return $this->hasOne('App\Domains\Crm\Models\SfAcccount', 'Id', 'Trainer_2__c');
    }

    public function Trainer3()
    {
        return $this->hasOne('App\Domains\Crm\Models\SfAcccount', 'Id', 'Trainer_3__c');
    }

    public function ScheduleTrainer()
    {
        return $this->hasMany('App\Domains\Crm\Models\ScheduleTrainer', 'Schedule__c', 'Id');
    }

    public static function getMemberScheduleById($account_id = null, $option = [])
    {
        $now = Carbon::now(); //DB::enableQueryLog();
        $data = self::
        with([
//            'Contract' => function ($md) {
//                $md->select(['Id', 'StartDate', 'EndDate', 'Status', 'AccountId']);
//                $md->with([
//                    'Benefit' => function ($ct) {
//                    },
//                    // 'Club' => function($md){
//                    // },
//                ]);
//            },
            'PTAssign' => function ($md) {
                $md->select(['Id', 'Name', 'LastName', 'FirstName']);
            },
            // 'ScheduleTrainer' => function ($md) {
            // },
            'Trainer1' => function ($md) {
                $md->select(['Id', 'Name','LastName','FirstName', 'Nickname__c']);
            },
            'Trainer2' => function ($md) {
                $md->select(['Id', 'Name','LastName','FirstName']);
            },
            'Trainer3' => function ($md) {
                $md->select(['Id', 'Name','LastName','FirstName']);
            },
            // 'Studio' => function($md){
            // },
            // 'Accounts' => function($md){
            //     // $md->select(['Id', 'Name','LastName','FirstName']);
            // },
//             'Club' => function($md){
//                 // $md->select(['Id', 'Name','LastName','FirstName']);
//             },
        ])
            //->withCount('Accounts')

            ->withCount(['ScheduleHV' => function ($md) {
                $md->where('HV_Status__c', 'Booked');
            }])
            ->withCount(['ScheduleHV as Registered_Guest__count' => function ($md) use ($account_id) {
                $md->where('HV_Status__c', 'Booked');
                $md->where('Account__c', $account_id);
                $md->where('Is_Guest__c', 1);
            }])
            ->withCount(['ScheduleHV as userBooked' => function ($md) use ($account_id) {
                $md->where('HV_Status__c', 'Booked')
                    ->where('Account__c', $account_id)
                    ->where('Is_Guest__c', 0);
            }])

            // ->withSum('ScheduleHV', 'Registered_Guest__c')
            // ->whereHas('Contract', function($q) use ($now,$account_id){
            //     $q->where('EndDate', '>=', $now->toDateTimeString());
            //     $q->where('Status', 'Activated');
            //     $q->where('AccountId', $account_id);
            // })
            ->whereHas('RecordType', function ($r) use ($option) {
                if (!empty($option['recordType'])) {
                    $r->where('DeveloperName', $option['recordType']['DeveloperName']);
                    $r->where('SobjectType', $option['recordType']['SobjectType']);
                }
            })
            ->whereHas('Club', function ($q) use ($now, $account_id, $option) {
                if (!empty($option['club'])) {
//                    $q->where('Name', 'like', "%" . $option['club'] . "%");
                    $q->whereIn('Id', explode(",", $option['club']));
                }
            })
            ->where('IsDeleted', 0)
            ->where(function ($stqr) use ($option) {
                if ( isset($option['user']) && $option['user']->hasRole('MS') ) $stqr->where(function ($msqr){
                    $msqr->where('Status__c', 'In Progress')
                        ->orWhere('Status__c', 'Open');
                });
                else $stqr->where('Status__c', 'Open');
            })
            ->where(function ($q) use ($now, $account_id, $option) {

                if (!empty($option['search'])) {
                    $q->where('Name', 'like', "%" . $option['search'] . "%");
                    $q->orWhere('Club_Name__c', 'like', "%" . $option['search'] . "%");
                    $q->orWhere('Class_Name__c', 'like', "%" . $option['search'] . "%");
                }
                if (!empty($option['club'])) {
                    $q->whereIN('Club__c', explode(',', $option['club']));
                }
                if (!empty($option['class'])) {
                    $q->whereIN('Class__c', explode(',', $option['class']));
                }
                if (!empty($option['class_group'])) {
                    // $q->where('Class_Group__c', 'like', "%" . $option['class_group'] . "%");
                    $q->whereIN('Class_Group__c', explode('|', $option['class_group']));
                }
                if (!empty($option['time'])) {
                    $q->whereTime('Start__c', '<=', $option['time']);
                    $q->whereTime('End__c', '>=', $option['time']);
                }
                if (!empty($option['date'])) {
                    $q->whereDate('Start__c', $option['date']);
                } else {
                    $q->where('Start__c', '>=', $now->toDateTimeString());
                }
                if ((isset($option['startTime']) && !empty($option['StartTime'])) || (isset($option['endTime']) && !empty($option['endTime']))) {

                    $q->where(function ($qq) use ($option) {
                        $qq->where(function ($qq1) use ($option) {
                            $qq1->whereTime('End__c', '>=', $option['startTime']);
                            $qq1->whereTime('End__c', '<=', $option['endTime']);
                        });
                        $qq->orWhere(function ($qq2) use ($option) {
                            $qq2->whereTime('Start__c', '>=', $option['startTime']);
                            $qq2->whereTime('Start__c', '<=', $option['endTime']);
                        });
                        $qq->orWhere(function ($qq3) use ($option) {
                            $qq3->whereTime('Start__c', '>=', $option['startTime']);
                            $qq3->whereTime('End__c', '<=', $option['endTime']);
                        });
                    });
                }

            })
            ->orderBy('Start__c', 'asc')
            ->paginate($option['per_page'] ?? 15, ['*'], 'page', $option['page']);
        // ->get();
        //dd(DB::getQueryLog());
        return $data;
    }

    /**
     * lấy toàn bộ danh sách Schedule
     * @param array $option
     */
    public static function listSchedule($option = []) { //DB::enableQueryLog();
        $now = Carbon::now();
        $data = self::
        with([
            'PTAssign' => function ($md) {
                $md->select(['Id', 'Name', 'LastName', 'FirstName']);
            },
            'Trainer1' => function ($md) {
                $md->select(['Id', 'Name','LastName','FirstName']);
            },
            'Trainer2' => function ($md) {
                $md->select(['Id', 'Name','LastName','FirstName']);
            },
            'Trainer3' => function ($md) {
                $md->select(['Id', 'Name','LastName','FirstName']);
            },
        ])
            /**
             * Nov-09-2021 - https://beunik.atlassian.net/browse/CIOS-215
             * lấy recordtype = Lich Giảng Dạy
             */
        ->whereHas('RecordType', function ($r) use ($option) {
            if (!empty($option['recordType'])) {
                $r->where('DeveloperName', $option['recordType']['DeveloperName']);
                $r->where('SobjectType', $option['recordType']['SobjectType']);
            }
        })
        ->where('IsDeleted', 0)
        ->where('Status__c', 'Open')
        ->where(function ($q) use ($now, $option) {

            if (!empty($option['search'])) {
                $q->where('Name', 'like', "%" . $option['search'] . "%");
                $q->orWhere('Club_Name__c', 'like', "%" . $option['search'] . "%");
                $q->orWhere('Class_Name__c', 'like', "%" . $option['search'] . "%");
            }
            if (!empty($option['club'])) {
                $q->whereIN('Club__c', array_filter(explode(',', $option['club'])));
            }
            if (!empty($option['class'])) {
                $q->whereIN('Class__c', explode(',', $option['class']));
            }
            if (!empty($option['class_group'])) {
                // $q->where('Class_Group__c', 'like', "%" . $option['class_group'] . "%");
                $q->whereIN('Class_Group__c', array_filter(explode('|', $option['class_group'])));
            }
            if (!empty($option['time'])) {
                $q->whereTime('Start__c', '<=', $option['time']);
                $q->whereTime('End__c', '>=', $option['time']);
            }
            if (!empty($option['date'])) {
                $optDate = Carbon::parse($option['date']);
                $q->whereYear('Start__c', $optDate->format('Y'));
                $q->whereMonth('Start__c', $optDate->format('m'));
                $q->whereDay('Start__c', $optDate->format('d'));
            } else {
                $q->where('Start__c', '>=', $now->toDateTimeString());
            }
            if ((isset($option['startTime']) && !empty($option['StartTime'])) || (isset($option['endTime']) && !empty($option['endTime']))) {

                $q->where(function ($qq) use ($option) {
                    $qq->where(function ($qq1) use ($option) {
                        $qq1->whereTime('End__c', '>=', $option['startTime']);
                        $qq1->whereTime('End__c', '<=', $option['endTime']);
                    });
                    $qq->orWhere(function ($qq2) use ($option) {
                        $qq2->whereTime('Start__c', '>=', $option['startTime']);
                        $qq2->whereTime('Start__c', '<=', $option['endTime']);
                    });
                    $qq->orWhere(function ($qq3) use ($option) {
                        $qq3->whereTime('Start__c', '>=', $option['startTime']);
                        $qq3->whereTime('End__c', '<=', $option['endTime']);
                    });
                });
            }

        })
        ->orderBy('Start__c', 'asc')->get(); //dd(DB::getQueryLog());
        return $data;
    }
}
