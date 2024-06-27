<?php

namespace App\Domains\TmtSfObject\Classes;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class ApexRest
{
    /**
     * Call Apex REST GET
     */
    public static function get($endPoint, $data = null)
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

            if ($data) {
                $requestOption[RequestOptions::QUERY] = $data;
            }

            $url = Utilities::buildApexRESTUrl($token, $endPoint);

            $response = $client->request('GET', $url, $requestOption);
            return $response;
        } else {
            throw new SalesforceException('Missing Salesforce authorization token');
        }
    }

    /**
     * Call Apex REST POST
     */
    public static function post($endPoint, $data = null)
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

            if ($data) {
                $requestOption[RequestOptions::JSON] = $data;
            }

            $url = Utilities::buildApexRESTUrl($token, $endPoint);

            $response = $client->request('POST', $url, $requestOption);
            return $response;
        } else {
            throw new SalesforceException('Missing Salesforce authorization token');
        }
    }

    /**
     * Call Apex REST PUT
     */
    public static function put($endPoint, $data = null)
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

            if ($data) {
                $requestOption[RequestOptions::JSON] = $data;
            }

            $url = Utilities::buildApexRESTUrl($token, $endPoint);

            $response = $client->request('PUT', $url, $requestOption);
            return $response;
        } else {
            throw new SalesforceException('Missing Salesforce authorization token');
        }
    }

    /**
     * Call Apex REST PATCH
     */
    public static function patch($endPoint, $data = null)
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

            if ($data) {
                $requestOption[RequestOptions::JSON] = $data;
            }

            $url = Utilities::buildApexRESTUrl($token, $endPoint);

            $response = $client->request('PATCH', $url, $requestOption);
            return $response;
        } else {
            throw new SalesforceException('Missing Salesforce authorization token');
        }
    }
}
