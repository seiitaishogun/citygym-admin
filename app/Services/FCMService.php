<?php
/**
 * @author tmtuan
 * created Date: 29-Mar-21
 */
namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Cache;
use Log;

class FCMService
{
    private $apiConfig;
    private $appInfoPath;

    public function __construct($app = 'sale')
    {
        $this->apiConfig = config('firebase');
        $this->appType = $app;
        if ( $app == 'sale' ) {
            $this->appInfoPath = $this->apiConfig['sale_client_path'];
            $this->projectId = $this->apiConfig['sale_projectId'];
        }
        else if( $app == 'member' ) {
            $this->appInfoPath = $this->apiConfig['member_client_path'];
            $this->projectId = $this->apiConfig['member_projectId'];
        }
    }


    /**
     * get google FCM token
     * @param int $retry
     * @return mixed
     * @throws \Exception
     */
    public function getToken($retry = 5)
    {
        if ($retry < 0) {
            throw new \Exception("Error when trying to get Firebase token");
        }
        $minutes = 30;
        $token = Cache::get('firebaseToken_'.$this->appType, function () use ($minutes, $retry) {
            $scope = config('firebase.scopes');

            $client = new \Google_Client();
            $client->useApplicationDefaultCredentials();
            $client->addScope($scope);
            $client->setAuthConfig($this->appInfoPath);
            $result = $client->fetchAccessTokenWithAssertion();
            if (!empty($result) && isset($result['access_token'])) {
                Cache::put('firebaseToken_'.$this->appType, $result, $minutes);
                return $result;
            } else {
                return $this->getToken($retry - 1);
            }
        });

        return $token;
    }

    /**
     * send Message to app user
     * @param $title
     * @param $message
     * @param $user_group
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function pushNotification($title, $message, $user_group, $dataOption = '')
    {
        try {
            $token = $this->getToken();
            $client = new Client();
            $clientHandler = $client->getConfig('handler');

            $data = [
                'message' => [
                    'topic' => $user_group,
                    // 'topic' => 'notification',
                    'notification' => [
                        'title' => $title,
                        'body' => $message
                    ],
                    //"data" => [
                    //    "Object" => "Mario",
                    //    "Id" => "PortugalVSDenmark"
                    //],
                    'android' => [
                        'notification' => [
                            'title' => $title,
                            'body' => $message,
                            'icon' => 'ic_notification',
                            // 'icon' => 'http://beunik.com.vn/demo/na/themes/unik/assets/frontend/images/ic_launcher.png',
                            // 'color' => '#0000ff'
                            // 'image' => 'http://beunik.com.vn/demo/na/themes/unik/assets/frontend/images/ic_launcher.png',
                            'color' => '#13FAEE'
                        ]
                    ],
                    'webpush' => [
                        'headers' => [
                            'Urgency' => 'high'
                        ],
                        'notification' => [
                            'body' => $message,
                            'requireInteraction' => 'true',
                            'badge' => '/badge-icon.png'
                        ]
                    ]
                ]
            ];

            if ( !empty($dataOption) && is_array($dataOption) ) $data['message']['data'] = $dataOption;

            try {
                $response = $client->request('POST', $this->apiConfig['api_endpoint'] . $this->projectId . '/messages:send', [
                    'handler' => $clientHandler,
                    'json' => $data,
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token['access_token']
                    ]
                ]);
                if ($response->getStatusCode() == '200') {
                    $content = $response->getBody()->getContents();
                    //log
                    activity('CMS-FCM')
                        ->withProperties(['Push Notification' => $data])
                        ->log('Push Notification| send success  #'.$content);
                    return ['success' => true];
                }

            } catch (ClientException $e) {
                $mess = $e->getResponse()->getBody()->getContents();
                $mess1 = json_decode($mess);
                $logdata = json_encode([ 'fb_error_mess' => $mess1, 'post_data' => $data]);
                \Log::error($logdata);
                return ['error' => $mess1, 'success' => false];
            }

        } catch (\Exception $e) {
            \Log::error($e);
            return ['error' => $e->getMessage(), 'success' => false];
        }
    }
}
