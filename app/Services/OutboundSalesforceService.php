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
class OutboundSalesforceService
{

    /**
     * OutboundSalesforceService constructor.
     */
    public function __construct()
    {
        
    }

    /**
     * 
     * @param Mixed $message
     * @param Array $attributes
     * @return Mixed Salesforce Id or false;
     */
    public function sendToSalesforce($message, $attributes = array())
    {
        $module = isset($attributes["module"]) ? $attributes["module"] : false;
        $operation = isset($attributes["operation"]) ? $attributes["operation"] : false;
        if ($module !== false) {
            $function = isset($attributes["function"]) ? $attributes["function"] : 'fetch';
            $processedData = $this->processMessage($module, $message);
            return $mapedObject = $this->mapData($module, $processedData, $function);
        }

        if ($operation !== false) {
            $mappedData = json_decode($message, true);
            return $this->sendMessageToSalesforce($operation, $mappedData);
        }
    }

    /**
     * 
     * @param type $operation
     * @param type $message
     * @param type $objId
     * @return type
     */
    private function sendMessageToSalesforce($operation, $message, $objId = false)
    {
        //var_dump($message);
        $mappedData = $message;
        $objectName = $mappedData['object'];
        if (!empty($mappedData["ZOHOID"])) {
            foreach ($mappedData["ZOHOID"] as $rel => $val)
            {
                $Id = $this->getObjectId($objectName, $rel, $val);
            }               
            if ($Id !== false) {
                $mappedData['fields']["Id"] = $Id;
            } else {
                $mappedData['fields'][$rel] = $val;
                unset($mappedData['fields']["Id"]);
            }
        }
        if (!empty($mappedData['fields'])) {
            foreach ($mappedData['fields'] as $key => $value) {
                if (is_array($value)) {
                    $mappedValue = $this->getMappedId($value);
                    $mappedData['fields'][$key] = ($mappedValue !== false) ? $mappedValue : '';
                }
            }
        }

        $objectId = (isset($mappedData['fields']["Id"])) ? $mappedData['fields']["Id"] : false;
        if (isset($mappedData['parentRelation']) && $objId) {
            $mappedData['fields'][$mappedData['parentRelation']] = $objId;
//            if($operation == 'update') unset($mappedData['fields'][$mappedData['parentRelation']]);
            $Id = $this->getObjectId($objectName, $mappedData['parentRelation'], $objId);
            if ($Id !== false) {
                $mappedData['fields']["Id"] = $Id;
                unset($mappedData['fields'][$mappedData['parentRelation']]);
            }
        }

        switch ($operation) {
            case 'update':
                $objectId = $this->processUpdate($objectName, (object) $mappedData['fields']);
                break;
            case 'updateornew':
                if (empty($mappedData['fields']['Id']) || $mappedData['fields']['Id'] == false) {
                    unset($mappedData['fields']['Id']);
                    if (!empty($mappedData["newfields"])) {
                        foreach ($mappedData["newfields"] as $key => $val)
                            $mappedData['fields'][$key] = $val;
                    }
                    $objectId = $this->processInsert($objectName, (object) $mappedData['fields']);
                } else {
                    $objectId = $this->processUpdate($objectName, (object) $mappedData['fields']);
                }
                //var_dump($objectId);
                //var_dump($mappedData['fields']);
                break;
        }

        if (!empty($message['childs']) && $objectId) {
            foreach ($message['childs'] as $child){
                $this->sendMessageToSalesforce($operation, $child, $objectId);
            }  
        }


        return $objectId;
    }

    /**
     * 
     * @param type $value
     * @return boolean
     */
    private function getMappedId($value)
    {
        $objectName = $value['object'];
        $cond = '';
        if (!empty($value['relations'])) {
            $cond = ' WHERE ' . $this->createConditionString($value['relations']);
        }
        $query = 'SELECT Id from ' . $objectName . $cond;
        $response = Salesforce::query(($query));

        if (count($response->records) > 0)
            foreach ($response->records as $record) {
                return $record->Id;
            }
        return false;
    }

    /**
     * 
     * @param type $relations
     * @param type $type
     * @return type
     */
    private function createConditionString($relations = array(), $type = 'AND')
    {
        $cond = '';
        if (!empty($relations)) {
            foreach ($relations as $relationType => $relation) {
                $string = '';
                if (is_array($relation))
                    $string = $this->createConditionString($relation, $relationType);
                else
                    $string = "$relationType = '$relation'";
                if (!empty($cond))
                    $cond = $cond . ' ' . $type . ' ' . ($string);
                else
                    $cond = ($string);
            }
        }
        return $cond;
    }

    /**
     * 
     * @param type $module
     * @param type $message
     * @return type
     */
    private function processMessage($module, $message)
    {
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

    /**
     * 
     * @param type $module
     * @param type $data
     * @param type $function
     * @return boolean
     */
    private function mapData($module, $data, $function = 'fetch')
    {
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

    /**
     * 
     * @param type $objectName
     * @param type $salesForceObject
     * @param type $cond
     * @param type $curd
     * @return type
     */
    private function processObject($objectName, $salesForceObject, $cond = [], $curd = 'fetch')
    {
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

    /**
     * 
     * @param type $objectName
     * @param type $salesForceObject
     * @return boolean
     */
    private function processInsert($objectName, $salesForceObject)
    {
        try {
            $createResponse = Salesforce::create(array($salesForceObject), $objectName);
            $returnResponse = $createResponse[0];
            if ($returnResponse->success)
                return $returnResponse->id;

            return $returnResponse->success;
        } catch (\Exception $ex) {
            return false;
        }
    }

    /**
     * 
     * @param type $objectName
     * @param type $salesForceObject
     * @return boolean
     */
    private function processUpdate($objectName, $salesForceObject)
    {
        try {
            $updateResponse = Salesforce::update(array($salesForceObject), $objectName);
            $returnResponse = $updateResponse[0];
            echo '<pre>';
            var_dump($returnResponse);
            echo '</pre>';
            if ($returnResponse->success)
                return $returnResponse->id;

            return $returnResponse->success;
        } catch (\Exception $ex) {
            return false;
        }
    }

    /**
     * 
     * @param type $objectName
     * @param type $cond
     * @param type $select
     */
    private function processFetch($objectName, $cond, $select = 'all')
    {
        
    }

    /**
     * 
     * @param type $objectName
     * @param type $relation
     * @param type $value
     * @return boolean
     */
    private function getObjectId($objectName, $relation, $value)
    {
        try {
            $query = 'SELECT Id from ' . $objectName . ' WHERE ' . $relation . " = '" . $value . "'";
            $response = Salesforce::query(($query));
            if (count($response->records) > 0)
                foreach ($response->records as $record) {
                    return $record->Id;
                }
            return false;
        } catch (\Exception $ex) {
            return false;
        }
    }

    /**
     * 
     */
    private function processDelete()
    {
        
    }

    /**
     * 
     * @param type $childs
     * @param type $parentId
     * @param type $data
     * @param type $function
     */
    private function processChild($childs, $parentId, $data, $function)
    {
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

    /**
     * 
     * @param type $parent
     * @param type $data
     * @return type
     */
    private function processParentFetch($parent, $data)
    {

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
