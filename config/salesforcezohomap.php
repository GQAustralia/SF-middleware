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
            'RTO Name' => 'Vendor__c',
            'Provider Code' => 'Provider_Code__c',
            'Description' => 'Description',
            'Modified Time' => 'LastModifiedDate',
            'Qualifications Id' => 'Qualifications_Id__c',
            'SalesforceId' => 'Id'
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
                'relations' => [
                    "parentId" => 'Product2Id'
                ]

            ]
        ],
        'parentObjects' => [
            [
                'object' => 'Account',
                'fields' => [
                    'RTO Name' => 'Id'
                ],
                'relations' => [
                    'RTO Name' => 'Zoho_RTO_Id__c'
                ]
            ]
        ]
        
    ],
    'Portfolio' => [
        'object' => 'Enrollment__c',
        'fields' => [
            '100 Points ID Received' =>  'X100_Points_ID_Received__c',
            'ACCOUNT LINKAGE' =>  '',
            'Active in System' =>  '',
            'Ad' =>  '',
            'Ad Campaign Name' =>  '',
            'Ad Click Date' =>  '',
            'Ad Network' =>  '',
            'AdGroup Name' =>  '',
            'ADGROUPID' =>  '',
            'ADID' =>  '',
            'AFFILIATION TYPE' =>  '',
            'Amount' =>  '',
            'Applicant Proceeding' =>  '',
            'Application Submission Date' =>  '',
            'Assessment Result' =>  '',
            'Australian or Overseas Experience' =>  '',
            'Balance' =>  '',
            'Balance Amount' =>  '',
            'Business Name' =>  '',
            'Category' =>  '',
            'Certificate Postage Ref.' =>  '',
            'Chart Start URL' =>  '',
            'Chat Transcript' =>  '',
            'Click Type' =>  '',
            'Client Id' =>  '',
            'Client Name' =>  '',
            'Closed' =>  '',
            'Closing Date' =>  '',
            'Company / Organisation' =>  '',
            'Company Id' =>  '',
            'Company Name' =>  '',
            'Conversion Export Status' =>  '',
            'Cost per Click' =>  '',
            'Cost per Conversion' =>  '',
            'Cost Price' =>  '',
            'Country' =>  '',
            'Created By' =>  '',
            'Created Time' =>  '',
            'Currency' =>  '',
            'Date of Birth' =>  '',
            'Date of Refund' =>  '',
            'Debit Success Account Num' =>  '',
            'Deposit Amount' =>  '',
            'Description' =>  '',
            'Device Type' =>  '',
            'Duration' =>  '',
            'email' =>  '',
            'Email ID' =>  '',
            'Employer' =>  '',
            'Evidence Tracking Start' =>  '',
            'Exchange Rate' =>  '',
            'Expected Revenue' =>  '',
            'Facilitated By' =>  '',
            'Faculty' =>  '',
            'Fax.' =>  '',
            'Final Quote $' =>  '',
            'Financial Stage Updated On' =>  '',
            'GA Search Keywords' =>  '',
            'GADCONFIGID' =>  '',
            'GCLID' =>  '',
            'Heard About Us' =>  '',
            'Historical Heard About Us' =>  '',
            'In-Depth Source' =>  '',
            'Inbound Source' =>  '',
            'Interaction ID' =>  '',
            'Invoice Date' =>  '',
            'Invoice Due Date' =>  '',
            'Invoice Number' =>  '',
            'Invoice Paid Date' =>  '',
            'Invoice Received Date' =>  '',
            'Invoice Sent' =>  '',
            'Job Title' =>  '',
            'Keyword' =>  '',
            'KEYWORDID' =>  '',
            'Last Activity Time' =>  '',
            'Layout' =>  '',
            'Lead Conversion Time' =>  '',
            'Lead Creation Date' =>  '',
            'Lead Source' =>  '',
            'Level and Qualification Achieved' =>  '',
            'Linked to Performance New' =>  '',
            'Mail Address as above' =>  '',
            'Message' =>  '',
            'Mobile' =>  '',
            'Modified By' =>  '',
            'Modified Time' =>  '',
            'Name of Bulk Co. if applicable' =>  '',
            'Occupation' =>  '',
            'Other Qualification / Industry Certification' =>  '',
            'Other Qualifications Age' =>  '',
            'Other Qualifications Name' =>  '',
            'Overall Sales Duration' =>  '',
            'Override Cost Update' =>  '',
            'Override Synch' =>  '',
            'Payment Method' =>  '',
            'Payment Option' =>  '',
            'Payment Plan Serial' =>  '',
            'Payment received' =>  '',
            'Payment Reference Number' =>  '',
            'Phone' =>  '',
            'PO Box or Street' =>  '',
            'Portfolio Id' =>  '',
            'Portfolio Name' =>  '',
            'Portfolio Owner' =>  '',
            'Portfolio Stage' =>  '',
            'Post Code' =>  '',
            'Potential Name' =>  '',
            'Probability' =>  '',
            'Promotion voucher No. if applicable' =>  '',
            'Province' =>  '',
            'Qual Sent To Applicant' =>  '',
            'Qualification Demanded' =>  '',
            'Qualification Demanded Code' =>  '',
            'Qualification Issued Date' =>  '',
            'Qualification Price' =>  '',
            'Qualification Quoted Price' =>  '',
            'Qualification Received by Candidate' =>  '',
            'Qualification Received by GQ' =>  '',
            'Re-Enrolled Date' =>  '',
            'Reason For Contact' =>  '',
            'Reason For Qualification' =>  '',
            'Reason for Refund' =>  '',
            'Referral Code' =>  '',
            'Referrer' =>  '',
            'Refund Amount' =>  '',
            'Refund Notes' =>  '',
            'Refund Requested Date' =>  '',
            'RPL Completed Date' =>  '',
            'RTO Invoice Number' =>  '',
            'Sales Cycle Duration' =>  '',
            'Search Partner Network' =>  '',
            'Secondary Email' =>  '',
            'Sent To RTO' =>  '',
            'Server Folder Location' =>  '',
            'Signed TC' =>  '',
            'Sold by' =>  '',
            'Source Description' =>  '',
            'Special Notes' =>  '',
            'Specific Source' =>  '',
            'Specification' =>  '',
            'Stage' =>  '',
            'Status' =>  '',
            'Street' =>  '',
            'Suburb' =>  '',
            'Target RTO' =>  '',
            'The Faculty' =>  '',
            'The Qualification Name' =>  '',
            'Time Running Out' =>  '',
            'Unit Price' =>  '',
            'update faculty' =>  '',
            'Using Voucher' =>  '',
            'Visitor ID' =>  '',
            'Welcome Call Completed' =>  '',
            'Years of Experience' =>  '',
            'SalesforceId' => 'Id'
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
                'relations' => [
                    'RTO Name' => 'Zoho_RTO_Id__c'
                ]
            ]
        ],
        'parentObjects' => [
            [
                'object' => 'Account',
                'fields' => [
                    'RTO Name' => 'Id'
                ],
                'relations' => [
                    'RTO Name' => 'Zoho_RTO_Id__c'
                ]
            ]
        ]
    ],
    'Leads' => [
        'object' => 'Lead',
        'fields' => [
            'Lead Owner' =>  '',
            'Active in the system' =>  '',
            'Ad' =>  '',
            'Ad Campaign Name' =>  '',
            'Ad Click Date' =>  '',
            'Ad Network' =>  '',
            'Address' =>  '',
            'AdGroup Name' =>  '',
            'ADGROUPID' =>  '',
            'ADID' =>  '',
            'Applicant Proceeding' =>  '',
            'Assessor' =>  '',
            'Australian or Overseas Experience' =>  '',
            'Australian Work Experience' =>  '',
            'Business Name' =>  '',
            'Campaign' =>  '',
            'Chart Start URL' =>  '',
            'Chat Transcript' =>  '',
            'City' =>  '',
            'Click Type' =>  '',
            'Client IP' =>  '',
            'Company' =>  '',
            'Contact Person' =>  '',
            'Contact Phone' =>  '',
            'Conversion Export Status' =>  '',
            'Conversion Exported On' =>  '',
            'Cost per Click' =>  '',
            'Cost per Conversion' =>  '',
            'Cost Price' =>  '',
            'Country' =>  '',
            'Created By' =>  '',
            'Created Time' =>  '',
            'Currency' =>  '',
            'Date of Birth' =>  '',
            'Date of Enquiry' =>  '',
            'Date Resume Received' =>  '',
            'Debit Success Account Num' =>  '',
            'Deposit Amount' =>  '',
            'Description' =>  '',
            'Device Type' =>  '',
            'Duplicate' =>  '',
            'Duration' =>  '',
            'Email' =>  '',
            'Email Address' =>  '',
            'Email Opt Out' =>  '',
            'Employer' =>  '',
            'Enquiry Time' =>  '',
            'Exchange Rate' =>  '',
            'Faculty' =>  '',
            'Fax' =>  '',
            'Fax.' =>  '',
            'Final Quote $' =>  '',
            'Final Quote Sent' =>  '',
            'First Name' =>  '',
            'Followed Up' =>  '',
            'Full Name' =>  '',
            'GA Campaign' =>  '',
            'GA Medium' =>  '',
            'GA Search Keywords' =>  '',
            'GA Source' =>  '',
            'GADCONFIGID' =>  '',
            'GCLID' =>  '',
            'Google Click ID' =>  '',
            'Heard About Us' =>  '',
            'High Level Skills' =>  '',
            'Historical Heard About Us' =>  '',
            'How Did you Hear About GQ?' =>  '',
            'In-Depth Source' =>  '',
            'Inbound Source' =>  '',
            'Interaction ID' =>  '',
            'Invoice Due Date' =>  '',
            'Invoice Number' =>  '',
            'Invoice Paid Date' =>  '',
            'Invoice Sent' =>  '',
            'Job Title' =>  '',
            'Keyword' =>  '',
            'KEYWORDID' =>  '',
            'LAMA' =>  '',
            'Last Activity Time' =>  '',
            'Last Name' =>  '',
            'Layout' =>  '',
            'Layout???' =>  '',
            'Lead Id' =>  '',
            'Lead Owner Id' =>  '',
            'Lead Source' =>  '',
            'Lead Status' =>  '',
            'LeadId' =>  '',
            'Level and Qualification Achieved' =>  '',
            'Linked to Performance' =>  '',
            'Mail Address as above' =>  '',
            'Mailing Postcode' =>  '',
            'Managing Managers Experience' =>  '',
            'Message' =>  '',
            'Mobile' =>  '',
            'Modified By' =>  '',
            'Modified Time' =>  '',
            'Name of Bulk Co. if applicable' =>  '',
            'Not Proceeding' =>  '',
            'Occupation' =>  '',
            'Other Qualifications' =>  '',
            'Other Qualifications Age' =>  '',
            'Other Qualifications Name' =>  '',
            'Payment Method' =>  '',
            'Payment Option' =>  '',
            'Payment Plan Serial' =>  '',
            'Payment Received' =>  '',
            'Phone' =>  '',
            'PO Box or Street' =>  '',
            'Post Code' =>  '',
            'Potential other Qualifications' =>  '',
            'Province' =>  '',
            'Pushed To CS' =>  '',
            'Qualification Demanded' =>  '',
            'Qualification Demanded Code' =>  '',
            'Qualification Level' =>  '',
            'Qualification Quoted Price' =>  '',
            'Qualified' =>  '',
            'Quoted Price' =>  '',
            'Rating' =>  '',
            'Read Terms and Cond' =>  '',
            'Reason for Contact' =>  '',
            'Reason for Conversion Failure' =>  '',
            'Reason For Qualification' =>  '',
            'Referral Code' =>  '',
            'Referrer' =>  '',
            'RTO' =>  '',
            'Sale Price' =>  '',
            'Salutation' =>  '',
            'Search Partner Network' =>  '',
            'Secondary Email' =>  '',
            'Skill Level' =>  '',
            'Skype ID' =>  '',
            'Sold By' =>  '',
            'Source' =>  '',
            'Source Description' =>  '',
            'Special Notes' =>  '',
            'Specific Source' =>  '',
            'State' =>  '',
            'State' =>  '',
            'Strategic Decision Making' =>  '',
            'Street' =>  '',
            'Suburb' =>  '',
            'Suburb/City/Town' =>  '',
            'Target RTO' =>  '',
            'The Faculty' =>  '',
            'The Qualification Name' =>  '',
            'Unit Price' =>  '',
            'Units & Training Package Sent' =>  '',
            'Using Voucher' =>  '',
            'Visitor ID' =>  '',
            'Work Phone' =>  '',
            'SalesforceId' => 'Id'
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
                'relations' => [
                    "parentId" => 'Product2Id'
                ]

            ]
        ],
        'parentObjects' => [
            [
                'object' => 'Account',
                'fields' => [
                    'RTO Name' => 'Id'
                ],
                'relations' => [
                    'RTO Name' => 'Zoho_RTO_Id__c'
                ]
            ]
        ]

    ],
    'Tasks' => [
        'object' => 'Task',
        'fields' => [
            'Qualifications Code' => 'ProductCode',
            'Qualifications Name' => 'Name',
            'Qualification Level' => 'Qualification_Level__c',
            'Qualifications Active' => 'Qualifications_Active__c',
            'Training Package' => 'Training_Package__c',
            'Usage Status' => 'Usage_Status__c',
            'View on training.gov.au' => 'View_on_training_gov_au__c',
            'Provided Online' => 'Provided_Online__c',
            'RTO Name' => 'Vendor__c',
            'Provider Code' => 'Provider_Code__c',
            'Description' => 'Description',
            'Modified Time' => 'LastModifiedDate',
            'Qualifications Id' => 'Qualifications_Id__c',
            'SalesforceId' => 'Id'
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
                'relations' => [
                    "parentId" => 'Product2Id'
                ]

            ]
        ],
        'parentObjects' => [
            [
                'object' => 'Account',
                'fields' => [
                    'RTO Name' => 'Id'
                ],
                'relations' => [
                    'RTO Name' => 'Zoho_RTO_Id__c'
                ]
            ]
        ]

    ],
];
