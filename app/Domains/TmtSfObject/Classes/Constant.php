<?php

namespace App\Domains\TmtSfObject\Classes;

final class Constant {
  const API_VERSION = 'v50.0';

  const CONNECT_TIMEOUT = 60;

  const O_AUTH_END_POINT = 'https://login.salesforce.com/services/oauth2/token';
  const O_AUTH_END_POINT_SB = 'https://test.salesforce.com/services/oauth2/token';

  const O_AUTH_CODE_END_POINT = 'https://login.salesforce.com/services/oauth2/authorize';
  const O_AUTH_CODE_END_POINT_SB = 'https://test.salesforce.com/services/oauth2/authorize';

  const REST_END_POINT = '/services/apexrest/';
  const SERVICE_DATA_END_POINT = 'services/data/';

  const SERVICE_OPERATION_RECORD = 'sobjects';
  const SERVICE_OPERATION_QUERY = 'query';
  const SERVICE_OPERATION_JOB = 'jobs/ingest';
  const SERVICE_OPERATION_COMPOSITE_RECORD = 'composite/sobjects';
  const SERVICE_OPERATION_DESCRIBE = 'describe';

  const GRANT_TYPE_CODE = 'authorization_code';
  const GRANT_TYPE_REFRESH_TOKEN = 'refresh_token';

  const SALESFORCE_SETTING_CODE = 'salesforce_settings';
}
