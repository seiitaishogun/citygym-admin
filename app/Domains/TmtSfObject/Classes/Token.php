<?php

namespace App\Domains\TmtSfObject\Classes;

class Token
{
    public $accessToken;
    public $instanceUrl;
    public $id;
    public $tokenType;
    public $issuedAt;
    public $signature;
    public $scope;
    public $refreshToken;

    public function __construct($accessToken, $instanceUrl, $id, $tokenType, $issuedAt, $signature)
    {
        $this->accessToken = $accessToken;
        $this->instanceUrl = $instanceUrl;
        $this->id = $id;
        $this->tokenType = $tokenType;
        $this->issuedAt = $issuedAt;
        $this->signature = $signature;
    }
}
