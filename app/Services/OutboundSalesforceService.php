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

        print_r($attributes);
        $xml = new \SimpleXMLElement($message);

        $converter = new XmlToJson\XmlToJsonConverter();
        $json = $converter->convert($xml);
        $jsonArray = \GuzzleHttp\json_decode($json);
        echo '<pre>';
        print_r($jsonArray);
        echo $json;
        exit();
        try {
            echo print_r(Salesforce::describeLayout('Account'), true);
        } catch (\Exception $e) {
            echo $e->getMessage();
            echo $e->getTraceAsString();
        }
    }

    private function processMessage() {
        
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
