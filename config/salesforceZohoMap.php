<?php

return [

    /*
      |--------------------------------------------------------------------------
      | Your Salesforce to ZOHO Mapping
      |--------------------------------------------------------------------------
      |
      |
     */
    'Products' => [
        'object' => 'Product2',
        'fields' => [
            'Qualifications Code' => 'ProductCode',
            'Qualifications Name' => 'Name',
            'Qualification Level' => 'Qualification_Level__c',
            'Qualifications Active' => 'Qualifications_Active__c',
            'Training Package' => 'Training_Package__c',
            'Usage Status' => 'Usage_Status__c',
            'View on training.gov.au' => 'View_on_training_gov_au__c',
            'Provided Online' => 'Provided_Online__c',
            'RTO Name' => '',
            'Provider Code' => 'Provider_Code__c',
            'Description' => 'Description',
            'Modified Time' => 'LastModifiedDate',
            'Qualifications Id' => 'Qualifications_Id__c'
        ],
        'childObjects' => [
            [
                'object' => 'PricebookEntry',
                'fields' => [
                    'Cost Price' => 'Cost_Price__c',
                    'Unit Price' => 'UnitPrice',
                    'Online Price' => 'Online_Price__c',
                    'Qualifications Id' => 'Zoho_Qualifications_Id__c'
                ],
                "parentId" => 'Product2Id'
            ]
        ],
        'parentObjects' => [
            
        ]
        
    ]
];
