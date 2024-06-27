<?php
/**
 * @author tmtuan
 * created Date: 16-Dec-20
 */
namespace App\Domains\TmtSfObject\Http\Controllers\Api;

use App\Domains\TmtSfObject\Classes\Authorization;
use App\Domains\TmtSfObject\Classes\Constant;
use App\Domains\TmtSfObject\Classes\SalesforceException;
use App\Domains\TmtSfObject\Classes\SObject;
use App\Domains\TmtSfObject\Classes\Utilities;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class LeadController extends Controller {
    public function getLeadStage() {
        $data = SObject::describe('Lead');
        echo '<pre>'; print_r($data);
    }
}
