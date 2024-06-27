<?php
/**
 * @author tmtuan
 * created Date: 25-Nov-20
 */

namespace App\Domains\Crm\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model {
    protected $table = 'salesforce_Contact';
    protected $primaryKey = 'Id';
    protected $casts = ['Id' => 'string'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Id', 'IsDeleted',   'MasterRecordId',  'AccountId',   'IsPersonAccount', 'LastName',    'FirstName',   'Salutation',  'MiddleName',
        'Suffix',  'Name',    'MailingStreet',   'MailingCity', 'MailingState',    'MailingPostalCode',   'MailingCountry',  'MailingLatitude', 'MailingLongitude',
        'MailingGeocodeAccuracy',  'MailingAddress',  'Phone',   'Fax', 'MobilePhone', 'ReportsToId', 'Email',   'Title',   'Department',  'OwnerId', 'CreatedDate',
        'CreatedById', 'LastModifiedDate',    'LastModifiedById',    'SystemModstamp',  'LastActivityDate',    'LastCURequestDate',   'LastCUUpdateDate',    'LastViewedDate',
        'LastReferencedDate',  'EmailBouncedReason',  'EmailBouncedDate',    'IsEmailBounced',  'PhotoUrl',    'Jigsaw',  'JigsawContactId', 'Relationship__c',
        'Relationship_Account__c', 'Country__c',  'Province__c', 'Street__c',   'Ward__c', 'Address_Full__c', 'District__c', 'Query', 'results', 'operations'
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public $timestamps = false;

}
