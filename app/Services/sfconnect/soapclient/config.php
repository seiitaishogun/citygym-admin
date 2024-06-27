<?php

//Defind connection parameters
//-------------------- Sandbox --------------------------
// define("BLINK_SANDBOX_USERNAME", "omn1@fvhospital.com.dev");
// define("BLINK_SANDBOX_PASSWORD", "S@lesforce1");
define("BLINK_SANDBOX_USERNAME", "omn1@fvhospital.com");
define("BLINK_SANDBOX_PASSWORD", "S@lesforce1");
define("BLINK_SANDBOX_WSDL_ENT", __DIR__."/dev/blink_product_enterprise.wsdl");
define("BLINK_SANDBOX_WSDL_PART", __DIR__."/blink_product_partner.wsdl");

//-------------------- Production --------------------------
define("BLINK_PRODUCTION_USERNAME", "omn1@fvhospital.com.dev2"); // Blink finance PRODUCTION
https://fvhospital--c.documentforce.com

define("BLINK_PRODUCTION_PASSWORD", "S@lesforce2"); // Blink finance PRODUCTION
define("BLINK_PRODUCTION_WSDL_ENT",  __DIR__."/blink_product_enterprise.wsdl");
//define("BLINK_PRODUCTION_WSDL_ENT", __DIR__."/dev/blink_product_enterprise.wsdl");
//define("BLINK_PRODUCTION_WSDL_ENT", __DIR__."/uat/blink_product_enterprise.wsdl");

//define("BLINK_PRODUCTION_WSDL_PART", __DIR__."/blink_sandbox_partner.wsdl");
define("BLINK_PRODUCTION_WSDL_PART", __DIR__."/blink_product_partner.wsdl");
?>
