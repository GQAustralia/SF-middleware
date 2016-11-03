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
        $module = isset($attributes["module"]) ? $attributes["module"] : false;
        if ($module !== false) {
            $function = isset($attributes["function"]) ? $attributes["function"] : 'fetch';
            if (($module == 'Products' || $module == 'Vendors') && ($function == 'upd'))
                $function = 'updnew';
            $processedData = $this->processMessage($module, $message);
            $mapedObject = $this->mapData($module, $processedData, $function);
        }
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
        try {
            $json = $converter->convert($xml);
            $jsonArray = \GuzzleHttp\json_decode($json, true);
            $objectArray = array();
            var_dump($jsonArray);
            echo $module;
            if ($module && isset($jsonArray[$module]) && isset($jsonArray[$module]["row"]) && isset($jsonArray[$module]["row"]["FL"])) {
                $dataArray = $jsonArray[$module]["row"]["FL"];
                //var_dump($dataArray);
                foreach ($dataArray as $data) {
                    $val = $data["-val"];
                    $text = isset($data["#text"]) ? $data["#text"] : '';
                    $objectArray[$val] = $text;
                }
               // var_dump($objectArray);
            }
            return $objectArray;
        } catch (\Exception $ex) {
            return $message;
        }
    }

    private function mapData($module, $data, $function = 'fetch') {
 //       var_dump($data);
        $map = config("salesforcezohomap.$module");
        if (!empty($map)) {
            $objectName = $map['object'];
            $parents = $map['parentObjects'];
            $childs = $map['childObjects'];
            $objectFileds = $map['fields'];
            $defaults = $map['default'];
            $relations = $map['relations'];
            $salesForceObject = new \stdClass();
            foreach ($defaults as $val => $default) {
                $salesForceObject->$default = (array_search($default, $data) !== false) ? $data[array_search($default, $data)] : $val;
            }
            foreach ($parents as $parent) {
                $parentResponse = $this->processParentFetch($parent, $data);
                if (!empty($parentResponse))
                    foreach ($parentResponse as $k => $v) {
                        if (isset($data[$k]))
                            $data[$k] = $v;
                    }
            }
            if (count($defaults) > 0) {
                foreach ($defaults as $val => $key) {
                    $salesForceObject->$key = $val;
                }
            }
            foreach ($data as $key => $val) {
                if (isset($objectFileds[$key]))
                    $salesForceObject->$objectFileds[$key] = $val;
            }
            $cond = [];
            foreach ($relations as $name => $relation) {
                $cond[$relation] = $name;
            }

            $objectId = $this->processObject($objectName, $salesForceObject, $cond, $function);
            if ($objectId) {
                $this->processChild($childs, $objectId, $data, $function);
            }
            return $objectId;
        }
        return false;
    }

    private function processObject($objectName, $salesForceObject, $cond = [], $curd = 'fetch') {
        //var_dump($salesForceObject);
        if (!isset($salesForceObject->Id)) {
            if (!empty($cond)) {
                foreach ($cond as $k => $v) {
                    if ($v == 'Id')
                        $salesForceObject->Id = $this->getObjectId($objectName, $k, $salesForceObject->$k);
                }
            }
        }
        switch ($curd) {
            case 'fetch' :
                return $this->processFetch($objectName, $cond, $select = 'all');
                break;
            case 'updnew' :
                if ((!isset($salesForceObject->Id)) || $salesForceObject->Id == false) {
                    if (isset($salesForceObject->Id))
                        unset($salesForceObject->Id);
                    return $this->processInsert($objectName, $salesForceObject);
                } else
                    return $this->processUpdate($objectName, $salesForceObject);
                break;
            case 'upd' :
                return $this->processUpdate($objectName, $salesForceObject);
                break;
        }
    }

    private function processInsert($objectName, $salesForceObject) {
        $createResponse = Salesforce::create(array($salesForceObject), $objectName);
        $returnResponse = $createResponse[0];
        if ($returnResponse->success)
            return $returnResponse->id;

        return $returnResponse->success;
    }

    private function processUpdate($objectName, $salesForceObject) {
        $updateResponse = Salesforce::update(array($salesForceObject), $objectName);
        $returnResponse = $updateResponse[0];
        echo '<pre>';
        var_dump($returnResponse);
        echo '</pre>';
        if ($returnResponse->success)
            return $returnResponse->id;

        return $returnResponse->success;
    }

    private function processFetch($objectName, $cond, $select = 'all') {
        
    }

    private function getObjectId($objectName, $relation, $value) {
        echo $query = 'SELECT Id from ' . $objectName . ' WHERE ' . $relation . " = '" . $value . "'";
        $response = Salesforce::query(($query));
//        echo '<pre>';
//        var_dump($response);
//        echo '</pre>';
        if (count($response->records) > 0)
            foreach ($response->records as $record) {
                return $record->Id;
            }
        return false;
    }

    private function processDelete() {
        
    }

    private function processChild($childs, $parentId, $data, $function) {
        foreach ($childs as $child) {
            $childObjectName = $child['object'];
            $childObject = new \stdClass();
            $fields = $child['fields'];
            $relations = $child['relations'];
            $defaults = $child['default'];
            foreach ($data as $key => $val) {
                if (isset($fields[$key]))
                    $childObject->$fields[$key] = $val;
            }
            $cond = [];
            foreach ($relations as $name => $relation) {
                if ($name == 'parentId') {
                    $cond[$relation] = $parentId;
                    $childObject->$relation = $parentId;
                } else
                    $cond[$relation] = $data[$name];
            }
            if (count($defaults) > 0) {
                foreach ($defaults as $val => $key) {
                    $childObject->$key = $val;
                }
            }
            $childId = $this->processObject($childObjectName, $childObject, $cond, $function);
            if (isset($child['childObjects'])) {
                $this->processChild($child['childObjects'], $childId, $data, $function);
            }
        }
    }

    private function processParentFetch($parent, $data) {

        $parentObjectName = $parent['object'];
        $parentrelations = $parent['relations'];
        $arr = [];
        if (!empty($parentrelations)) {
            foreach ($parentrelations as $k => $v) {
                if (isset($data[$k])) {
                    $Id = $this->getObjectId($parentObjectName, $v, $data[$k]);
                    $arr[$k] = $Id;
                }
            }
        }
        return $arr;
    }

}
