<?php

namespace App\Domains\TmtSfObject\Classes;

use Exception;

class SalesforceException extends Exception
{
  /**
   * {@inheritdoc}
   */
  protected $message = 'An error occurred';
}
