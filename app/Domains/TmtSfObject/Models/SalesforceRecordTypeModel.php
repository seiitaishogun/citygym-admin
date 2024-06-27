<?php
/**
 * @author tmtuan
 * created Date: 16-Dec-20
 */
namespace App\Domains\TmtSfObject\Models;

use Illuminate\Database\Eloquent\Model;

class SalesforceRecordTypeModel extends Model {
    protected $table = 'salesforce_Record_Type';
    protected $primaryKey = 'Id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Id', 'Name', 'DeveloperName', 'SobjectType'
    ];
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
}
