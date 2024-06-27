<?php
namespace App\Services\sfconnect;
// define("BLINK_SANDBOX_USERNAME", "omn1@fvhospital.com");
// define("BLINK_SANDBOX_PASSWORD", "S@lesforce1");
// define("BLINK_SANDBOX_WSDL_ENT", __DIR__."/soapclient/dev/blink_product_enterprise.wsdl");
// define("BLINK_SANDBOX_WSDL_PART", __DIR__."/soapclient/blink_product_partner.wsdl");

// define("BLINK_SANDBOX_USERNAME", "mm1@citigym.com.vn");
// define("BLINK_SANDBOX_PASSWORD", "Citigym@2021");
// define("BLINK_SANDBOX_USERNAME", "thobui279@force.com.qa");
// define("BLINK_SANDBOX_PASSWORD", "Citigym2021Ilz83RaYchU8dUoF3e9CQRbc");
define("BLINK_SANDBOX_USERNAME", "thobui279@force.com");
//define("BLINK_SANDBOX_PASSWORD", "Citigym@20217SEqU0bNjBXT4uGgabxGydE0");
define("BLINK_SANDBOX_PASSWORD", "Citigym@0107!fXhYlalnOP2MO7iyK4d7IvDkD");
define("BLINK_SANDBOX_WSDL_ENT", __DIR__."/soapclient/city_gym/enterprise-Pro.wsdl");
define("BLINK_SANDBOX_WSDL_PART", __DIR__."/soapclient/city_gym/blink_product_partner.wsdl");


class sfconnect
{
    public $result_con;
    public $result_msg;
    public $mySforceConnection;
    public function sfConnect($connection = "Sandbox",$type="Enterprise") {

        try {
            if($type == "Enterprise"){
                require_once (__DIR__."/soapclient/SforceEnterpriseClient.php");
                $this->mySforceConnection = new \SforceEnterpriseClient();
            }else{
                require_once (__DIR__."/soapclient/SforcePartnerClient.php");
                $this->mySforceConnection = new \SforcePartnerClient();
            }
            $this->result_con = true;
            if($connection=="Sandbox"){
                if($type == "Enterprise"){
                    $mySoapClient = $this->mySforceConnection->createConnection(BLINK_SANDBOX_WSDL_ENT);
                }else{
                    $mySoapClient = $this->mySforceConnection->createConnection(BLINK_SANDBOX_WSDL_PART);
                }

                $mylogin = $this->mySforceConnection->login(BLINK_SANDBOX_USERNAME, BLINK_SANDBOX_PASSWORD);
            } else {
                if($type == "Enterprise"){
                    $mySoapClient = $this->mySforceConnection->createConnection(BLINK_PRODUCTION_WSDL_ENT);
                }else{
                    $mySoapClient = $this->mySforceConnection->createConnection(BLINK_PRODUCTION_WSDL_PART);
                }
                $mylogin = $this->mySforceConnection->login(BLINK_PRODUCTION_USERNAME, BLINK_PRODUCTION_PASSWORD);
            }

        } catch (Exception $e) {
            $this->result_con = false;
            $this->result_msg = $e->faultstring;
        }
    }

    public function connectFail(){
        echo $this->result_msg;
    }

    public function callQuery($query = null){
        if ($query) {
            if(!isset($this->mySforceConnection)){
                try {
                    $this->sfConnect();
                } catch (\Exception $e) {
                    return ['success' => false, 'message' => $e->getMessage()];
                }

                // $this->sfConnect('PRODUCTION','Unlimited Edition');
            }
            if(isset($this->result_con) and $this->result_con){

                $response = $this->mySforceConnection->query($query);
                if($response->size == 1 || isset($response->records)){
                    return $response->records;
                }else{
                    return false;
                }
            }else{
                $this->connectFail();
            }
        }
    }


    public function describeSObject($query= null){
        if ($query) {
            if(!isset($this->mySforceConnection)){
                $this->sfConnect();
                // $this->sfConnect('PRODUCTION','Unlimited Edition');
            }
            if(isset($this->result_con) and $this->result_con){

                $response = $this->mySforceConnection->describeSObject($query)->fields;
                //DescribeLayoutResult = connection.describeLayout(string sObjectType, string layoutName, ID recordTypeID[]);
                if($response->size == 1 || isset($response->records)){
                    return $response->records;
                }else{
                    return false;
                }
            }else{
                $this->connectFail();
            }
        }
    }

    public function SelectList ( $objectType, $fieldName = null , $recordTypeID = null) {

            if(!isset($this->mySforceConnection)){
                $this->sfConnect();
                // $this->sfConnect('PRODUCTION','Unlimited Edition');
            }
            if(isset($this->result_con) and $this->result_con){

                //$result = $this->mySforceConnection->describeSObject($objectType); // get object
                //result = $this->mySforceConnection->describeLayout($objectType,'Referral Case',['0126F000000vv27QAA']);
                $result = $this->mySforceConnection->describeLayout($objectType, $recordTypeID); // get objcet with record type
                //print(json_encode($result));
                //die();
                $data = [];
                foreach ($result->recordTypeMappings[0]->picklistsForRecordType as $field) {

                    if (in_array($field->picklistName,$fieldName)) {
                        //print(json_encode($field));die;
                        $data[] = $field;
                    }

                }
                return $data;

            }else{
                $this->connectFail();
            }
    }

    public function create($data = null,$table){
        if ($data) {
            if(!isset($this->mySforceConnection)){
                $this->sfConnect();
            }
            if(isset($this->result_con) and $this->result_con){
                 if(!isset($_SESSION))
                    {
                        session_start();
                    }
                try
                {
                    //var_dump($data);die()
                    $respon = $this->mySforceConnection->create(array($data), $table);
                     //var_dump($respon);die(); // mo de xem loi
                    return $respon;
                }catch(Exception $e) {
                    // var_dump($e);
                    return false;
                }
            }else{
                $this->connectFail();
            }
        }
    }

    public function update($data = null,$table){
        if ($data) {
            if(!isset($this->mySforceConnection)){
                $this->sfConnect();
            }
            if(isset($this->result_con) and $this->result_con){
                session_start();
                try
                {
                    // var_dump($data);die();
                    $respon = $this->mySforceConnection->update(array($data), $table);
                    return $respon;
                }catch(Exception $e) {
                    var_dump($e);
                    return false;
                }
            }else{
                $this->connectFail();
            }
        }
    }
}

?>
