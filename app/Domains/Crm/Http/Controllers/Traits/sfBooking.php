<?php
/**
 * @author tmtuan
 * created Date: 30-Dec-20
 */
namespace App\Domains\Crm\Http\Controllers\Traits;

use App\Domains\TmtSfObject\Classes\SObject;
use Illuminate\Support\Facades\Session;

trait sfBooking {
    public function getSfBookingSetting() {
        $tConfigData = Session::get('t_config_info');
        if ( empty($tConfigData) ) {
            try {
                $rqData = SObject::query('SELECT Id, DeveloperName, ThoiLuongBuoiT__c, ThoiLuongBuoiF__c, SoBuoiT__c FROM Opportunity_Setting__mdt');
            } catch (\Exception $e) {
                return response()->json('Can\'t get config data! Please try again', 422);
            }

            $returnData = [
                "ThoiLuongBuoiT" => $rqData[0]['ThoiLuongBuoiT__c'],
                "ThoiLuongBuoiF" => $rqData[0]['ThoiLuongBuoiF__c'],
                "SoBuoiT" => $rqData[0]['SoBuoiT__c']
            ];
            Session::put('t_config_info', $returnData);
        } else $returnData = $tConfigData;
        return $returnData;
    }

}
