<?php
/**
 * @author tmtuan
 * created Date: 25-Nov-20
 */

namespace App\Domains\Crm\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ScheduleTrainer extends Model {
    protected $table = 'salesforce_Schedule_Trainer__c';
    protected $primaryKey = 'Id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Id', 'OwnerId', 'IsDeleted',   'Name',    'CreatedDate', 'CreatedById', 'LastModifiedDate',    'LastModifiedById',    'SystemModstamp',  'Account__c',  'Schedule__c', 'Trainer_Name__c', 'Start__c',    'End__c'
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public $timestamps = false;

//    public static function getScheduleById($account_id = null,$schedule_id = null){
//        if (!$account_id) {
//            return [];
//        }
//        $page = 1;
//        $limit = 100;
//
//        $selectColumns = [
//            'schv.Id booking_id',
//            'schv.Trainer_Name__c Trainer_Name__c',//Studio name
//            'sc.Name',
//            'sc.Capacity__c Capacity__c',
//            'sc.Class_Room_Capacity__c Class_Room_Capacity__c',
//            'sc.Class__c Class__c',
//            'sc.Class_Name__c Class_Name__c',
//            'sc.Class_Room_name__c Class_Room_name__c',
//            'sc.Club__c Club__c',
//            'sc.Club_Name__c Club_Name__c',
//            'sc.Contract__c Contract__c',
//            'sc.Duration__c Duration__c',
//            'sc.End__c End__c',
//            'sc.End_Update__c End_Update__c',
//            'sc.Start__c Start__c',
//            'sc.F_Level__c F_Level__c',
//            'sc.Class_Color__c Class_Color__c',
//            'sc.Class_Room_name__c Class_Room_name__c',//Studio name
//
//
//            'ac.Name AS account_name',
//        ];
//        $sql = 'SELECT ' . implode(',', $selectColumns) . ' ';
//
//        $sql .= 'FROM salesforce_Schedule_Trainer__c schv ';
//        $sql .= 'LEFT JOIN salesforce_Schedule__c sc ON (schv.Schedule__c = sc.Id) ';
//        $sql .= 'LEFT JOIN salesforce_Account ac ON (schv.Account__c = ac.Id) ';
//        $sql .= 'WHERE schv.Account__c = "'.$account_id.'" ';
//
//        if ($schedule_id) {
//            $sql .= 'AND schv.id = "'.$schedule_id.'" ';
//        }
//
//        $sql .= 'ORDER BY sc.Start__c DESC ';
//        $sql .= 'LIMIT ' . ((int) $limit * ((int) $page - 1)) . ', ' . (int) $limit;
//
//        $Bookings = DB::select($sql);
//
//
//        return $Bookings;
//    }
}
