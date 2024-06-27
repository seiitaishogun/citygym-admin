<?php

namespace App\Domains\TmtSfObject\Classes;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Exception\BadResponseException;

class SObject
{
    /**
     * SObject Describe
     *
     * https://developer.salesforce.com/docs/atlas.en-us.api_rest.meta/api_rest/resources_sobject_describe.htm
     *
     * @param string $sObjectName The sObject name
     *
     * @return array All metadata for an object
     *
     */
    public static function describe($sObjectName)
    {
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

            $url = Utilities::buildServiceDataUrl($token, Constant::SERVICE_OPERATION_RECORD . '/' . $sObjectName . '/' . Constant::SERVICE_OPERATION_DESCRIBE);

            $response = $client->request('GET', $url, $requestOption);
            if ($response->getStatusCode() == '200') {
                $content = $response->getBody()->getContents();
                $data = json_decode($content, true);
            } else {
                throw new SalesforceException('Error when trying to get sObject describe - [' . $sObjectName . ']');
            }
        } else {
            throw new SalesforceException('Missing Salesforce authorization token');
        }

        return $data;
    }

    /**
     * Run Salesforce SOQL query
     *
     * https://developer.salesforce.com/docs/atlas.en-us.api_rest.meta/api_rest/resources_query.htm
     *
     * @param string $soql Soql query string
     * @param bool $all Set this to true to get all records from SOQL query. Otherwise it will only return a part of the result.
     *
     * @return array Soql query result (List of records).
     */
    public static function query($soql, $all = false)
    {
        $data = [];
        $token = Authorization::getToken();
        if ($token) {
            $client = new Client();
            $requestOption = [
                RequestOptions::HEADERS => [
                    'Authorization' => 'Bearer ' . $token->accessToken
                ],
                RequestOptions::CONNECT_TIMEOUT => Constant::CONNECT_TIMEOUT
            ];

            $url = Utilities::buildServiceDataUrl($token, Constant::SERVICE_OPERATION_QUERY);
            $soql = preg_replace('/ /', '+', $soql);
            $requestOption[RequestOptions::QUERY] = 'q=' . $soql;

            $response = $client->request('GET', $url, $requestOption);
            if ($response->getStatusCode() == '200') {
                $content = $response->getBody()->getContents();
                $json = json_decode($content, true);
                $data = $json['records'];
                if ($all && isset($json['nextRecordsUrl']) && !empty($json['nextRecordsUrl'])) {
                    $next = true;
                    $nextRecordClient = new Client();
                    $nextRecordResult = $json;
                    while ($next) {
                        $nextRecordResponse = $nextRecordClient->request('GET', $nextRecordResult['nextRecordsUrl'], [
                            'headers' => [
                                'Authorization' => 'Bearer ' . $token['access_token']
                            ]
                        ]);

                        if ($nextRecordResponse->getStatusCode() == '200') {
                            $nextRecordResult = json_decode($nextRecordResponse->getBody()->getContents(), true);
                            $data = array_merge($data, $nextRecordResult['records']);
                            if (!isset($nextRecordResult['nextRecordsUrl']) || empty($nextRecordResult['nextRecordsUrl'])) {
                                $next = false;
                            }
                        } else {
                            $next = false;
                        }
                    }
                }
            } else {
                throw new SalesforceException('Error when trying to query Salesforce data - [' . $soql . ']');
            }
        } else {
            throw new SalesforceException('Missing Salesforce authorization token');
        }

        return $data;
    }

    /**
     * Call Salesforce insert record API
     *
     * https://developer.salesforce.com/docs/atlas.en-us.api_rest.meta/api_rest/resources_sobject_basic_info.htm
     *
     * @param string $sObjectName The name of the SObject to insert
     * @param array $recordData SObject record data, needed to have all required fields to create the record.
     *
     * @return string Inserted record id
     */
    public static function create($sObjectName, $recordData)
    {
        $token = Authorization::getToken();
        if ($token) {
            $client = new Client();
            $requestOption = [
                RequestOptions::HEADERS => [
                    'Authorization' => 'Bearer ' . $token->accessToken
                ],
                RequestOptions::CONNECT_TIMEOUT => Constant::CONNECT_TIMEOUT,
                RequestOptions::JSON => $recordData
            ];

            $url = Utilities::buildServiceDataUrl($token, Constant::SERVICE_OPERATION_RECORD) . '/' . $sObjectName;

            try {
                $response = $client->request('POST', $url, $requestOption);
            } catch ( BadResponseException $e ) {
                $response = $e->getResponse();
                $responseBody = $response->getBody()->getContents();
                $result = json_decode($responseBody);
                if ( isset($result[0]) ) {
                    $objResult = $result[0];
                    $objResult->status_code = $response->getStatusCode();
                } else {
                    $objResult = (object)[];
                    $objResult->status_code = $response->getStatusCode();
                }

                return $objResult;
            }

            if ($response->getStatusCode() == 201 || $response->getStatusCode() == 200) {
                $content = $response->getBody()->getContents();
                $result = json_decode($content, true);
                return $result['id'];
            } else {
                throw new SalesforceException('Error when trying to create ' . $sObjectName . ' - [' . $response->getBody()->getContents() . ']');
            }
        } else {
            throw new SalesforceException('Missing Salesforce authorization token');
        }
    }

    /**
     * Call Salesforce API to insert a list of records
     *
     * https://developer.salesforce.com/docs/atlas.en-us.api_rest.meta/api_rest/resources_composite_sobjects_collections_create.htm
     *
     * @param string $allOrNone Indicates whether to roll back the entire request when the creation of any object fails (true) or to continue with the independent creation of other objects in the request.
     * @param array $recordData A list of sObjects.
     *
     * @return array Insert result
     */
    public static function createMultiple($records, $allOrNone = true)
    {
        $token = Authorization::getToken();
        if ($token) {
            $client = new Client();
            $requestOption = [
                RequestOptions::HEADERS => [
                    'Authorization' => 'Bearer ' . $token->accessToken
                ],
                RequestOptions::CONNECT_TIMEOUT => Constant::CONNECT_TIMEOUT,
                RequestOptions::JSON => [
                    'allOrNone' => $allOrNone,
                    'records' => $records
                ]
            ];

            $url = Utilities::buildServiceDataUrl($token, Constant::SERVICE_OPERATION_COMPOSITE_RECORD);

            $response = $client->request('POST', $url, $requestOption);
            if ($response->getStatusCode() == 200) {
                $content = $response->getBody()->getContents();
                $result = json_decode($content, true);
                return $result;
            } else {
                throw new SalesforceException('Error when trying to create composite records - [' . $response->getBody()->getContents() . ']');
            }
        } else {
            throw new SalesforceException('Missing Salesforce authorization token');
        }
    }

    /**
     * Call Salesforce update record API
     *
     * https://developer.salesforce.com/docs/atlas.en-us.api_rest.meta/api_rest/resources_sobject_basic_info.htm
     *
     * @param string $sObjectName The name of the SObject to update
     * @param string $recordId The id of the record to update
     * @param array $recordData SObject record data.
     *
     * @return bool Return true if update successfully
     */
    public static function update($sObjectName, $recordId, $recordData)
    {
        $token = Authorization::getToken();
        if ($token) {
            $client = new Client();
            $requestOption = [
                RequestOptions::HEADERS => [
                    'Authorization' => 'Bearer ' . $token->accessToken
                ],
                RequestOptions::CONNECT_TIMEOUT => Constant::CONNECT_TIMEOUT,
                RequestOptions::JSON => $recordData
            ];

            $url = Utilities::buildServiceDataUrl($token, Constant::SERVICE_OPERATION_RECORD) . '/' . $sObjectName . '/' . $recordId;

            $response = $client->request('PATCH', $url, $requestOption);
            if ($response->getStatusCode() >= 200 && $response->getStatusCode() <= 300) {
                return true;
            } else {
                return false;
            }
        } else {
            throw new SalesforceException('Missing Salesforce authorization token');
        }
    }

    /**
     * Call Salesforce API to update a list of records
     *
     * https://developer.salesforce.com/docs/atlas.en-us.api_rest.meta/api_rest/resources_composite_sobjects_collections_update.htm
     *
     * @param string $allOrNone Indicates whether to roll back the entire request when the creation of any object fails (true) or to continue with the independent creation of other objects in the request.
     * @param array $recordData A list of sObjects.
     *
     * @return array Insert result
     */
    public static function updateMultiple($records, $allOrNone = true)
    {
        $token = Authorization::getToken();
        if ($token) {
            $client = new Client();
            $requestOption = [
                RequestOptions::HEADERS => [
                    'Authorization' => 'Bearer ' . $token->accessToken
                ],
                RequestOptions::CONNECT_TIMEOUT => Constant::CONNECT_TIMEOUT,
                RequestOptions::JSON => [
                    'allOrNone' => $allOrNone,
                    'records' => $records
                ]
            ];

            $url = Utilities::buildServiceDataUrl($token, Constant::SERVICE_OPERATION_COMPOSITE_RECORD);

            $response = $client->request('PATCH', $url, $requestOption);
            if ($response->getStatusCode() == 200) {
                $content = $response->getBody()->getContents();
                $result = json_decode($content, true);
                return $result;
            } else {
                throw new SalesforceException('Error when trying to create composite records - [' . $response->getBody()->getContents() . ']');
            }
        } else {
            throw new SalesforceException('Missing Salesforce authorization token');
        }
    }

    /**
     * Call Salesforce upsert record API
     *
     * https://developer.salesforce.com/docs/atlas.en-us.api_rest.meta/api_rest/resources_sobject_basic_info.htm
     *
     * @param string $sObjectName The name of the SObject to insert
     * @param string $externalField The api name of the external id field. We can set this to Id to upsert without external id field
     * @param string $id The id to update record
     * @param array $recordData SObject record data, needed to have all required fields to create the record.
     *
     * @return string Upserted record id
     */
    public static function upsert($sObjectName, $externalField, $id, $recordData)
    {
        $token = Authorization::getToken();
        if ($token) {
            $client = new Client();
            $requestOption = [
                RequestOptions::HEADERS => [
                    'Authorization' => 'Bearer ' . $token->accessToken
                ],
                RequestOptions::CONNECT_TIMEOUT => Constant::CONNECT_TIMEOUT,
                RequestOptions::JSON => $recordData
            ];

            $url = Utilities::buildServiceDataUrl($token, Constant::SERVICE_OPERATION_RECORD) . '/' . $sObjectName . '/' . $externalField . (!empty($id) ? '/' . $id : '');

            $response = $client->request((!empty($id) ? 'PATCH' : 'POST'), $url, $requestOption);
            $statusCode = $response->getStatusCode();
            if ($statusCode == 201 || $statusCode == 204 || $statusCode == 200) {
                $content = $response->getBody()->getContents();
                $result = json_decode($content, true);
                return $result['id'];
            } else {
                throw new SalesforceException('Error when trying to upsert ' . $sObjectName . ' - [' . $response->getBody()->getContents() . ']');
            }
        } else {
            throw new SalesforceException('Missing Salesforce authorization token');
        }
    }

    /**
     * Call Salesforce delete record API
     *
     * https://developer.salesforce.com/docs/atlas.en-us.api_rest.meta/api_rest/dome_delete_record.htm
     *
     * @param string $sObjectName The name of the SObject to update
     * @param string $recordId The id of the record to update
     *
     * @return bool Return true if delete successfully
     */
    public static function delete($sObjectName, $recordId)
    {
        $token = Authorization::getToken();
        if ($token) {
            $client = new Client();
            $requestOption = [
                RequestOptions::HEADERS => [
                    'Authorization' => 'Bearer ' . $token->accessToken
                ],
                RequestOptions::CONNECT_TIMEOUT => Constant::CONNECT_TIMEOUT
            ];

            $url = Utilities::buildServiceDataUrl($token, Constant::SERVICE_OPERATION_RECORD) . '/' . $sObjectName . '/' . $recordId;

            $response = $client->request('DELETE', $url, $requestOption);
            if ($response->getStatusCode() == 204) {
                return true;
            } else {
                return false;
            }
        } else {
            throw new SalesforceException('Missing Salesforce authorization token');
        }
    }
}
