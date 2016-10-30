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
            $objectName = $map['object'];
            $parents = $map['parentObjects'];
            $childs = $map['childObjects'];
            $objectFileds = $map['fields'];
            $defaults = $map['default'];
            $salesForceObject = new \stdClass();
            foreach($defaults as $val => $default) {
                $salesForceObject->$default = (array_search($default) !== false)?$data[array_search($default)]:$val;
            }
            foreach($parents as $parent) {
                $parentObjectName = $parent['object'];
                $parentObject = new \stdClass();
                $fields = $parent['fields'];
                $relations = $parent['relations'];
                $cond = [];
                $select = [];
                foreach ($fields as $name => $field) {
                    $select[$name] = $field;
                }
                foreach ($relations as $name => $relation) {
                    $cond[$relation] = $data[$name];
                }
                $parentResponse = $this->processFetch($parentObjectName, $cond, $select);
                foreach($parentResponse as $pr) {
                    foreach($select as $name => $sel) {
                        $data[$name] = $sel;
                    }
                }
            }
            foreach($data as $key=>$val){
                if(isset($objectFileds[$key]))
                $salesForceObject->$objectFileds[$key] = $val;
            }
            $objectId = $this->processObject($salesForceObject);
            foreach($childs as $child) {
                $childObjectName = $parent['object'];
                $childObject = new \stdClass();
                $fields = $child['fields'];
                $relations = $child['relations'];
                $cond = [];
                $select = [];
                
            }
        }
    }
    
    
    private function processObject($salesForceObject) {
        
    }

    private function processInsert() {
        
    }

    private function processUpdate() {
        
    }

    private function processFetch($objectName,$cond,$select='all') {
        
    }

    private function processDelete() {
        
    }

}
