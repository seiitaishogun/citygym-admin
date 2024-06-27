<?php
namespace App\Domains\Crm\Http\Controllers\Api\Tests;

use App\Domains\Auth\Models\User;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;

class TestController extends ApiController {
    public function testTest(Request $request) {
        try {
            $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => 'https://omn1solution.com/event/test.php',
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_POSTFIELDS => array('test' => '1'),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            echo $response;            
        } catch (Exception $e) {
            var_dump($e);
        }
    }
    public function testTestNo(Request $request) {
        var_dump('fff');die();
    }
}
