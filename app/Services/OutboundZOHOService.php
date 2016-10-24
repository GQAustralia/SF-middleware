<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Services;

use GuzzleHttp\Client;
/**
 * Description of ZOHOService
 *
 * @author samir.mohapatra
 */
class OutboundZOHOService {
    
    private $zohoScope;
    private $zohoAuthToken;
    private $zohoVersion;
    private $client;

    public function __construct() {
        $this->zohoScope = env('zohoScope','crmapi');
        $this->zohoAuthToken = env('zohoAuthToken','ff5196138d9b9112b7fe675a9c6025d0');
        $this->zohoVersion = env('zohoVersion',4);
        $this->client = new Client;
    }
    public function sendToZOHO($message,$attributes=array()) {
        $function = isset($attributes['function'])?$attributes['function']:'';
        $module = isset($attributes['module'])?$attributes['module']:'';
        $response_type = isset($attributes['response_type'])?$attributes['response_type']:'xml';
        if(empty($function) || empty($module) || empty($response_type)) return false;
        $zohoURI = $this->getZOHOUrl($function, $module,$response_type);
         //        $ins_parameter = $this->utlObj->setParameter("scope", $this->SCOPE, $ins_parameter);
 //        $ins_parameter = $this->utlObj->setParameter("authtoken", $this->AUTHTOKEN, $ins_parameter);
 //        $ins_parameter = $this->utlObj->setParameter("newFormat", 1 , $ins_parameter);
 //        $ins_parameter = $this->utlObj->setParameter("xmlData", $xml_data , $ins_parameter);
         //$response = $this->utlObj->sendCurlRequest($this->utlObj->get_url('ins','Products','json'), $ins_parameter);
//                 $response = $this->GQSQS->sendToSQS(['ins','Products','json'],$xml_data,[
//                     "newFormat" => 1
//                 ],true);

        $request = $this->client->post($zohoURI)
    ->setPostField('scope', $this->zohoScope)
    ->addPostFile('authtoken', $this->zohoAuthToken)
    ->addPostFile('xmlData', $this->zohoAuthToken);    

$response = $request->send();
var_dump($response);
print_r($response);
    }
    public function getZOHOUrl($function, $module, $response_type = 'xml') {
        switch ($function) {
              case 'get':
                  $url = 'https://crm.zoho.com/crm/private/' . $response_type . '/' . $module . '/getRecords';
                  break;
  
              case 'del':
                  $url = 'https://crm.zoho.com/crm/private/' . $response_type . '/' . $module . '/deleteRecords';
                  break;
  
              case 'ins':
                  $url = 'https://crm.zoho.com/crm/private/' . $response_type . '/' . $module . '/insertRecords';
                  break;
  
              case 'upd':
                  $url = 'https://crm.zoho.com/crm/private/' . $response_type . '/' . $module . '/updateRecords';
                  break;
 
             default :
                 die("INVALID OPERATION / FUNCTION");
         }
         return $url;
    }

}
