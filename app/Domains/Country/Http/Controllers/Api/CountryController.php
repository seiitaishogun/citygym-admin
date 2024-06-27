<?php
/**
 * @author tmtuan
 * created Date: 17-Nov-20
 */

namespace App\Domains\Country\Http\Controllers\Api;

use App\Domains\Country\Models\Country;
use App\Domains\Country\Models\District;
use App\Domains\Country\Models\Province;
use App\Domains\Country\Models\Ward;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class CountryController extends ApiController {

    public function importData1(Request $request) {
        // $queryData = $request->all();
        $data = $request->json()->all();

        $_country = Country::where('value', 'vn')->first();
        if (!$_country) {
            $_country = new Country;
            $_country->name = 'Việt Nam';
            $_country->value = 'vn';
            $_country->save();
        }

        foreach ($data as $itemData) {
            // tinh_thanh
            $_province = Province::where('country_id', $_country->id)
            ->where('value', $itemData['ma_TP'])->first();
            if (!$_province) {
                $_province = new Province;
                $_province->country_id = $_country->id;
                $_province->name = $itemData['tinh_thanh'];
                $_province->value = $itemData['ma_TP'];
                $_province->save();
            }

            // quan_huyen
            $_district = District::where('province_id', $_province->id)
            ->where('value', $itemData['ma_QH'])->first();
            if (!$_district) {
                $_district = new District;
                $_district->province_id = $_province->id;
                $_district->name = $itemData['quan_huyen'];
                $_district->value = $itemData['ma_QH'];
                $_district->save();
            }

            //phuong_xa
            $_ward = Ward::where('district_id', $_district->id)
            ->where('value', $itemData['ma_PX'])->first();
            if (!$_ward) {
                $_ward = new Ward;
                $_ward->district_id = $_district->id;
                $_ward->name = $itemData['phuong_xa'];
                $_ward->value = $itemData['ma_PX'];
                $_ward->type = $itemData['cap'];
                $_ward->save();
            }
        }
        return 1;
    }

    public function importData(Request $request) {
        $data = $request->json()->all();
        foreach ($data as $itemData) {
            $sf_id = $itemData['Id'];
            $type = $itemData['Level__c'];
            $name = $itemData['Name'];

            $affected = Ward::where('type', $type)
                          ->where('name', $name)
                          ->update(['sf_id' => $sf_id]);
        }
        return 1;        
    }

}
