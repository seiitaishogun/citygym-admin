<?php
/**
 * @author tmtuan
 * created Date: 25-Nov-20
 */

namespace App\Domains\Crm\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class SupportCase extends Model {
    protected $table = 'salesforce_Case';
    protected $primaryKey = 'Id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Id', 'IsDeleted',	'MasterRecordId',	'CaseNumber',	'ContactId',	'AccountId',	'ParentId',	'SuppliedName',
        'SuppliedEmail',	'SuppliedPhone',	'SuppliedCompany',	'Type',	'RecordTypeId',	'Status',	'Reason',	'Origin',
        'Language',	'Subject',	'Priority',	'Description',	'IsClosed',	'ClosedDate',	'IsEscalated',	'OwnerId',	'CreatedDate',
        'CreatedById',	'LastModifiedDate',	'LastModifiedById',	'SystemModstamp',	'ContactPhone',	'ContactMobile',	'ContactEmail',
        'ContactFax',	'Comments',	'LastViewedDate',	'LastReferencedDate', 'Club__c', 'Contract__c',	'Refund_Amount__c',	'Admin_Fee__c',
        'Last_Payment_Date__c',	'Process_Day__c',	'EstimatedEndDate__c',	'Process_Status__c',	'Phone__c',	'Amount__c',	'Fee__c',
        'Reason__c',	'Deactive_Date__c',	'Suspend_Date__c',	'Reason_Suspend__c',	'Other_Reason__c',	'Suspend_Duration__c',
        'Suspend_Expired__c',	'Old_Club__c',	'New_Club__c',	'Transfer_Contract__c',	'Transfer_Remaining_Amount__c',	'Transfer_Amount__c',
        'Receive_Contract__c',	'Receive_Amount__c',	'Old_Start_Date__c',	'New_Starting_Date__c',	'Contract_Code__c',	'Case_Type__c',
        'Case_Details__c',	'Description__c',	'Case_Source__c',	'Outbound_Detail__c',	'Occuring_Date__c',	'Level_1__c',	'Level_2__c',
        'Level_3__c',	'Benificiary_Account__c',	'Related_Department__c',	'ACC_Approval__c',	'Delay_Start_Days__c',	'MSR_Type__c',
        'MSM_Approval__c',	'Query', 'results', 'operations'
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public $timestamps = false;
    protected $casts = ['Id' => 'string'];
    public function Club()
    {
        return $this->hasOne('App\Domains\Crm\Models\Club','Id','Club__c');
    }     
}
