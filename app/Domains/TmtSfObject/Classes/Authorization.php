<?php

namespace App\Domains\TmtSfObject\Classes;

use Illuminate\Support\Facades\Cache;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use App\Models\Settings;

class Authorization
{
    const CACHE_DURATION = 60;
    const TOKEN_CACHE_KEY = 'SALESFORCE_TOKEN';

    public static function buildAuthorizationCodeUrl($settings)
    {
        $url = $settings->is_sandbox ? Constant::O_AUTH_CODE_END_POINT_SB : Constant::O_AUTH_CODE_END_POINT;

        if (empty($settings->client_id)) {
            throw new SalesforceException("Please save your connected app client id first.");
        }

        $params = [
            'client_id=' . $settings->client_id,
            'redirect_uri=' . url('admin/salesforce/oauth2/callback', [], true),
            'response_type=code',
            'scope=refresh_token full'
        ];

        return $url . '?' . implode('&', $params);
    }

    public static function getToken()
    {
        $settingData = Settings::where('item', Constant::SALESFORCE_SETTING_CODE)->first();

        $token = Cache::get(self::TOKEN_CACHE_KEY, function () use($settingData) {
            $settings = json_decode($settingData->value);

            $result = self::authenticate($settings->client_id, $settings->client_secret, $settings->refresh_token, Constant::GRANT_TYPE_REFRESH_TOKEN, $settings->is_sandbox);

            Cache::put(self::TOKEN_CACHE_KEY, $result, self::CACHE_DURATION);
            return $result;
        });

        return $token;
    }

    public static function authenticate($clientId, $clientSecret, $code, $grantType, $isSandbox = false, $oauthEnpoint = null)
    {
        $client = new Client();
        $option = [
            RequestOptions::CONNECT_TIMEOUT => Constant::CONNECT_TIMEOUT
        ];
        $result = [];
        $data = [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => $grantType
        ];

        if ($grantType == Constant::GRANT_TYPE_CODE) {
            $data['code'] = $code;
            $data['redirect_uri'] = url('admin/salesforce/oauth2/callback', [], true);
        }

        if ($grantType == Constant::GRANT_TYPE_REFRESH_TOKEN) {
            $data['refresh_token'] = $code;
        }

        $url = $isSandbox ? Constant::O_AUTH_END_POINT_SB : Constant::O_AUTH_END_POINT;
        if ($oauthEnpoint) {
            $url = $oauthEnpoint;
        }

        try {
            $option[RequestOptions::FORM_PARAMS] = $data;

            $response = $client->request('POST', $url, $option);
            if ($response->getStatusCode() == '200') {
                $content = $response->getBody()->getContents();
                $result = json_decode($content, true);
            }
        } catch (RequestException $e) {
            var_dump($e);die();
            throw new SalesforceException("Salesforce authentication request exception [" . $e->getMessage() . "] - [" . $url . "]");
        } catch (\Throwable $e) {
            var_dump($e);die();
            throw new SalesforceException("Salesforce authentication exception [" . $e->getMessage() . "] - [" . $url . "]");
        }

        if (!empty($result) && isset($result['access_token'])) {
            $token = new Token($result['access_token'], $result['instance_url'], $result['id'], $result['token_type'], $result['issued_at'], $result['signature']);
            if (isset($result['scope'])) {
                $token->scope = $result['scope'];
            }
            if (isset($result['refresh_token'])) {
                $token->refreshToken = $result['refresh_token'];
            }

            return $token;
        } else {
            throw new SalesforceException("Salesforce authentication failed.");
        }
    }
}
