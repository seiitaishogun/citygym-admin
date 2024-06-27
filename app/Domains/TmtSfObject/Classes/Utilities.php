<?php

namespace App\Domains\TmtSfObject\Classes;

final class Utilities
{
  public static function buildServiceDataUrl(Token $token, $operation): string
  {
    return $token->instanceUrl . '/' . Constant::SERVICE_DATA_END_POINT . Constant::API_VERSION .  '/' . $operation;
  }

  public static function buildApexRESTUrl( $token, $endpoint): string
  {
    return $token->instanceUrl . '/' . Constant::REST_END_POINT . $endpoint;
  }
}
