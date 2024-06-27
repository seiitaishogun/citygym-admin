<?php
/**
 * @author tmtuan
 * created Date: 01-Dec-20
 */

namespace App\Domains\Crm\Http\Controllers\Api\Users;

use App\Domains\Crm\Models\Contract;
use App\Domains\Crm\Models\Lead;
use App\Domains\Crm\Models\RecordType;
use App\Domains\Crm\Models\Schedule;
use App\Domains\Crm\Models\UserKpi;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use function Couchbase\defaultDecoder;

class UserKpiController extends ApiController{
    protected $ngayDauKi = 21;
    protected $soBuoiDayChuan = 6;
    protected $saleLeadDefault = 5;

    /**
     * Task: https://beunik.atlassian.net/browse/CIT-624 - Phần 1 - Show dạy
     * @param Request $request
     * @return mixed
     */
    public function getPTSession(Request $request) {
        $user = auth()->user();
        //Son Add Role Sale,Pt
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);
        if ( empty($user) ) return response()->json([
            'message' => 'Invalid Request'
        ], 400);

        //get F Session record Type
        $recordType = RecordType::where('SobjectType', 'Schedule__c')
            ->where('DeveloperName', 'F_Booking')->get()->first();

        //calculate start perios date
        $dateData = $this->calDaysInPerios();
        $soBuoiChuan = $dateData['difNum']/7*$this->soBuoiDayChuan;

        //get F count number
        $fCount = Schedule::where('RecordTypeId', $recordType->Id)->where('Account_Name__c', $user->sf_account_id())
                ->whereBetween('CreatedDate', [$dateData['tdate'], $dateData['fdate']])->count();

        $response = [
            'number_of_session' => $fCount,
            'number_of_default_session' => $soBuoiChuan
        ];
        return response()->json($response, 200);
    }

    /**
     * calculate the number of days in perios
     * lấy ngày hiện tại tới ngày đầu kì gần nhất ( vd: hôm nay là 23/8 thì lấy từ 22/8-21/8) ***
     * @return mixed
     */
    private function calDaysInPerios() {
        $todaycheck = Carbon::today();
        $checkDay = Carbon::createFromFormat('m/d/Y', $todaycheck->format('m').'/'.$this->ngayDauKi.'/'.$todaycheck->format('Y'));
        if ( $todaycheck->greaterThan($checkDay) ) {
            $fdate = $todaycheck->subDays(1);
            $tdate = $checkDay->copy()->startOfDay();
            $difInterval = $fdate->diff($tdate);
            $daysPerios = $difInterval->format('%a');
            $response = [
                'fdate' => $fdate,
                'tdate' => $tdate,
                'difNum' => $daysPerios,
            ];
        } else {
            $fdate = $todaycheck->subDays(1);
            $newMonthVal = $checkDay->subMonth()->format('m');
            $tdate = Carbon::createFromFormat('m/d/Y', $newMonthVal.'/'.$this->ngayDauKi.'/'.$todaycheck->format('Y')); //dd($fdate, $tdate);
            $difInterval = $fdate->diff($tdate);
            $daysPerios = $difInterval->format('%a');
            $response = [
                'fdate' => $fdate,
                'tdate' => $tdate,
                'difNum' => $daysPerios,
            ];
        }
        return $response;
    }

    /**
     * Task: https://beunik.atlassian.net/browse/CIT-624 - Phần 2 - Buổi tập F phát sinh trong kỳ
     * ngày trong kỳ lấy giống phần trên ***
     * @return mixed
     */
    public function getPTSessionInPeriod(Request $request) {
        $user = auth()->user();
        //Son Add Role Sale,Pt
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);        
        if ( empty($user) ) return response()->json([
            'message' => 'Invalid Request'
        ], 400);
        $sf_account = $request->account;
        //get F Session record Type
        $recordType = RecordType::where('SobjectType', 'Schedule__c')
            ->where('DeveloperName', 'F_Booking')->get()->first();

        //calculate start perios date
        $dateData = $this->calDaysInPerios();
        //DB::enableQueryLog();
        $fCount = Schedule::select(['Id', 'Name'])
                ->where('RecordTypeId', $recordType->Id)->where('PT_Assign__c', $sf_account)
                ->whereBetween('CreatedDate', [$dateData['tdate'], $dateData['fdate']])->get(); //dd(DB::getQueryLog());
        return response()->json($fCount, 200);
    }

    /**
     * Task: https://beunik.atlassian.net/browse/CIT-624 - Phần 3 - BR cho MB
     * cần thêm 2 tham số
     * accountID - default lấy current user login account id***
     * date - default lấy yesterday date*** -> format chuẩn 'm/d/Y'
     * @return mixed
     */
    public function getSaleBR(Request $request) {
        $user = auth()->user();
        //Son Add Role Sale,Pt
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);        
        if ( empty($user) ) return response()->json([
            'message' => 'Invalid Request'
        ], 400);

        $accountId = $request->account??$user->sf_account??$user->sf_account_id();
        $date = (isset($request->date) && !empty($request->date)) ? Carbon::createFromFormat('m/d/Y',$request->date): Carbon::today();

        //get sale lead in day
        $saleLead = Lead::select(['Id', 'Name'])->where('Staff_Assignment__c', $accountId)->whereDate('CreatedDate', $date)->count();

        return response()->json(['n1' => $saleLead, 'default_lead' => $this->saleLeadDefault], 200);
    }

    /**
     * Task: https://beunik.atlassian.net/browse/CIT-624 - Phần 4 - Package size
     *
     * @param Request $request
     * @return mixed
     */
    public function getPackageSize(Request $request)
    {
        $user = auth()->user();
        //Son Add Role Sale,Pt
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);        
        if ( empty($user) ) return response()->json([
            'message' => 'Invalid Request'
        ], 400);

        $account = $request->account??$user->sf_account??$user->sf_account_id();

        //get contract
        $contractCount = Contract::join('salesforce_Contract_Sale__c', 'salesforce_Contract_Sale__c.Contract__c', '=', 'salesforce_Contract.Id')
            ->select(['Id'])
            ->where('salesforce_Contract_Sale__c.Sales__c', $account)
            ->whereIn('salesforce_Contract.Status', ['Approved', 'Activated', 'Suspend'])
            ->where('salesforce_Contract.Contract_Status__c', 'New')
            ->count();
        $contractTotalAmount = Contract::join('salesforce_Contract_Sale__c', 'salesforce_Contract_Sale__c.Contract__c', '=', 'salesforce_Contract.Id')
            ->select(['Id', 'Actual_Amount__c'])
            ->where('salesforce_Contract_Sale__c.Sales__c', $account)
            ->whereIn('salesforce_Contract.Status', ['Approved', 'Activated', 'Suspend'])
            ->where('salesforce_Contract.Contract_Status__c', 'New')
            ->sum('salesforce_Contract.Actual_Amount__c');

        //dd($contractCount, $contractTotalAmount);

        if ( $contractCount > 0 ) return response()->json($contractTotalAmount/$contractCount, 200);
        else return response()->json(0, 200);
    }
}
