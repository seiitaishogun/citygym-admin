<?php
/**
 * @author tmtuan
 * created Date: 25-Nov-20
 */

namespace App\Domains\Crm\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Event extends Model {
    protected $table = 'salesforce_Event';
    protected $primaryKey = 'Id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    'RecordTypeId',    'WhoId',   'WhatId',  'WhoCount',    'WhatCount',   'Subject', 'Location',    'IsAllDayEvent',   'ActivityDateTime',    'ActivityDate',    'DurationInMinutes',   'StartDateTime',   'EndDateTime', 'EndDate', 'Description', 'AccountId',   'OwnerId', 'Type',    'IsPrivate',   'ShowAs',  'IsDeleted',   'IsChild', 'IsGroupEvent',    'GroupEventType',  'CreatedDate', 'CreatedById', 'LastModifiedDate',    'LastModifiedById',    'SystemModstamp',  'IsArchived',  'RecurrenceActivityId',    'IsRecurrence',    'RecurrenceStartDateTime', 'RecurrenceEndDateOnly',   'RecurrenceTimeZoneSidKey',    'RecurrenceType',  'RecurrenceInterval',  'RecurrenceDayOfWeekMask', 'RecurrenceDayOfMonth',    'RecurrenceInstance',  'RecurrenceMonthOfYear',   'ReminderDateTime',    'IsReminderSet',   'EventSubtype',    'IsRecurrence2Exclusion',  'Recurrence2PatternText',  'Recurrence2PatternVersion',   'IsRecurrence2',   'IsRecurrence2Exception',  'Recurrence2PatternStartDate', 'Recurrence2PatternTimeZone',  'Class__c',    'Class_Group__c',  'Classroom__c',    'Club__c', 'Trainer__c',  'Capacity__c', 'Class_Code__c',   'Member_Check_In__c',  'MS_Check_In__c',  'PT_Check_In__c',  'Card__c', 'Contract__c' 
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

}
