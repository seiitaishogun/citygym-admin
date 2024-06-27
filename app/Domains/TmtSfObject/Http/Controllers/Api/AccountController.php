<?php
/**
 * @author tmtuan
 * created Date: 16-Dec-20
 */
namespace App\Domains\TmtSfObject\Http\Controllers\Api;

use App\Domains\TmtSfObject\Classes\Authorization;
use App\Domains\TmtSfObject\Classes\Constant;
use App\Domains\TmtSfObject\Classes\SalesforceException;
use App\Domains\TmtSfObject\Classes\Utilities;
use App\Domains\TmtSfObject\Models\SalesforceRecordTypeModel;
use App\Http\Controllers\Controller;
use App\Domains\TmtSfObject\Classes\SObject;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class AccountController extends Controller {
    public function updateAccount() {
        $updateData = [
//            "Name"=> "tmtuan TEst",
            "LastName"=> "tmtuan",
            "FirstName"=> "TEst",
            "DOB__c"=> "2020-12-16",
            "Gender__c"=> "Male",
            "PersonMobilePhone"=> "123456789",
            "Pref_Mobile__c"=> "123456789",
            "Job_Title__c"=> "Sale",
            "Title__c"=> "Mr sale man",
//            "Street__pc"=> "string",
//            "Country__pc"=> "string",
//            "District__pc"=> "string",
//            "Province__pc"=> "string",
//            "Ward__pc"=> "string"
        ];
//        $result = SObject::update('Account', '0010l00001AKAJ5AAP', $updateData);

        $data = null;
        $token = Authorization::getToken();
        if ($token) {
            $client = new Client();
            $requestOption = [
                RequestOptions::HEADERS => [
                    'Authorization' => 'Bearer ' . $token->accessToken
                ],
                RequestOptions::CONNECT_TIMEOUT => Constant::CONNECT_TIMEOUT
            ];

            $url = Utilities::buildServiceDataUrl($token, Constant::SERVICE_OPERATION_QUERY . '?q=SELECT Id, Name, DeveloperName, SobjectType FROM RecordType');

            $response = $client->request('GET', $url, $requestOption);
            if ($response->getStatusCode() == '200') {
                $content = $response->getBody()->getContents();
                $data = json_decode($content, true);
            } else {
                throw new SalesforceException('Error when trying to get data');
            }
        } else {
            throw new SalesforceException('Missing Salesforce authorization token');
        }

        echo '<pre>'; print_r($data);
    }

    public function getRecordType() {
        $data = null;
        $token = Authorization::getToken();
        if ($token) {
            $client = new Client();
            $requestOption = [
                RequestOptions::HEADERS => [
                    'Authorization' => 'Bearer ' . $token->accessToken
                ],
                RequestOptions::CONNECT_TIMEOUT => Constant::CONNECT_TIMEOUT
            ];

            $url = Utilities::buildServiceDataUrl($token, Constant::SERVICE_OPERATION_QUERY . '?q=SELECT Id, Name, DeveloperName, SobjectType FROM RecordType');

            $response = $client->request('GET', $url, $requestOption);
            if ($response->getStatusCode() == '200') {
                $content = $response->getBody()->getContents();
                $data = json_decode($content, true);
            } else {
                throw new SalesforceException('Error when trying to get data');
            }
        } else {
            throw new SalesforceException('Missing Salesforce authorization token');
        }

        if ( !empty($data) ) {
            foreach ($data['records'] as $record) {
                $item = SalesforceRecordTypeModel::find($record['Id']);
                if ( !empty($item) ) {
                    $item->Name = $record['Name'];
                    $item->DeveloperName = $record['DeveloperName'];
                    $item->SobjectType  = $record['SobjectType'];
                    $item->updated_at  = date('Y-m-d H:i:s');
                    $item->save();

                    //log
                    activity('salesforce_api')
                        ->causedBy($item)
                        ->withProperties(['record_type' => $record])
                        ->log('Record Type| update Record Type\'s data success');
                } else {
                    $record['created_at'] = date('Y-m-d H:i:s');
                    $record['updated_at'] = date('Y-m-d H:i:s');
                    try {
                        $item = SalesforceRecordTypeModel::create($record);
                        echo "<p>Create record #{$record['Id']} success";

                        //log
                        activity('salesforce_api')
                            ->causedBy($item)
                            ->withProperties(['record_type' => $record])
                            ->log('Record Type| create new Record Type success');
                    } catch (\Exception $e) {
                        echo "<p>Can\'t not create record #{$record['Id']}";
                    }
                }
            }

        }
    }
}
