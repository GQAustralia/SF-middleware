<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Services;

use Salesforce;
use MarkWilson\XmlToJson;

/**
 * Description of Salesforce Service
 *
 * @author samir.mohapatra
 */
class OutboundSalesforceService {

    public function __construct() {
        
    }

    public function sendToSalesforce($message, $attributes = array()) {

        $a = config("salesforceZohoMap.token");
        $module = isset($attributes["module"])?$attributes["module"]:false;
        $processedData = $this->processMessage($module, $message);
        $mapedObject = $this->mapData($module,$processedData);
//        try {
//            echo print_r(Salesforce::describeLayout('Account'), true);
//        } catch (\Exception $e) {
//            echo $e->getMessage();
//            echo $e->getTraceAsString();
//        }
    }

    private function processMessage($module, $message) {
        
        $xml = new \SimpleXMLElement($message);
        
        $converter = new XmlToJson\XmlToJsonConverter();
        $json = $converter->convert($xml);
        $jsonArray = \GuzzleHttp\json_decode($json,true);
        $objectArray = array();
        if($module && isset($message[$module]) && isset($message[$module]["row"]) && isset($message[$module]["row"]["FL"])){
            
            $dataArray =  $message[$module]["row"]["FL"];
            
            foreach ($dataArray as $data) {
                
                $objectArray[$data["-val"]] = $objectArray[$data["#text"]];
            }
        }
        
        return $objectArray;
    }
    
    private function mapData($module,$data){
        $map = config("salesforceZohoMap.$module");
        if(!empty($map)){
            
        }
    }

    private function processInsert() {
        
    }

    private function processUpdate() {
        
    }

    private function processFetch() {
        
    }

    private function processDelete() {
        
    }

}
