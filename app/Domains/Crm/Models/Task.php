<?php
/**
 * @author tmtuan
 * created Date: 09-Dec-20
 */
namespace App\Domains\Crm\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model {
    protected $table = 'salesforce_Task';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        //force required
        'Id', 'WhoId','WhatId', 'OwnerId', 'AccountId', 'CreatedById', 'LastModifiedById', 'RecurrenceActivityId', 'Class__c', 'Class_Group__c', 'Classroom__c', 'Club__c',
        'Trainer__c', 'Card__c', 'Contract__c',

        'Subject', 'Status'
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public $timestamps = false;
    protected $casts = ['Id' => 'string'];
}
