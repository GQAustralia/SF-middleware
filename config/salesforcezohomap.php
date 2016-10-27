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
            '100 Points ID Received' => 'X100_Points_ID_Received__c',
            'Active in System' => 'Active_in_System__c',
            'Ad' => 'Ad__c',
            'Amount' => 'Amount__c',
            'Application Submission Date' => 'Application_Submission_Date__c',
            'Assessment Result' => 'Assessment_Result__c',
            'Australian or Overseas Experience' => 'Australian_or_Overseas_Experience__c',
            'Balance' => 'Balance__c',
            'Balance Amount' => 'Balance_Amount__c',
            'Business Name' => 'Business_Name__c',
            'Chart Start URL' => 'Chart_Start_URL__c',
            'Chat Transcript' => 'Chat_Transcript__c',
            'Client Id' => 'Client__c',
            'Closed' => 'Closed__c',
            'Closing Date' => 'Closing_Date__c',
            'Company Id' => 'Company__c',
            'Cost Price' => 'Cost_Price__c',
            'Country' => 'Country__c',
            'Created By' => 'CreatedById',
            'Created Time' => 'Created_Time__c',
            'Date of Birth' => 'Date_of_Birth__c',
            'Date of Refund' => 'Date_of_Refund__c',
            'Debit Success Account Num' => 'Debit_Success_Account_Num__c',
            'Deposit Amount' => 'Deposit_Amount__c',
//            'Description' => '',
            'Device Type' => 'Device_Type__c',
            'email' => 'email__c',
//            'Email ID' => '',
            'Employer' => 'Employer__c',
            'Evidence Tracking Start' => 'Evidence_Tracking_Start__c',
            'Faculty' => 'Faculty__c',
            'Final Quote $' => 'Final_Quote__c',
            'Financial Stage Updated On' => 'Financial_Stage_Updated_On__c',
            'GA Search Keywords' => 'GA_Search_Keywords__c',
            'GADCONFIGID' => 'GADCONFIGID__c',
            'Heard About Us' => 'Heard_About_Us__c',
            'Historical Heard About Us' => 'Historical_Heard_About_Us__c',
            'In-Depth Source' => 'In_Depth_Source__c',
            'Inbound Source' => 'Inbound_Source__c',
            'Invoice Date' => 'Invoice_Date__c',
            'Invoice Due Date' => 'Invoice_Due_Date__c',
            'Invoice Number' => 'Invoice_Number__c',
            'Invoice Paid Date' => 'Invoice_Paid_Date__c',
            'Keyword' => 'Keyword__c',
            'Last Activity Time' => 'Last_Activity_Time__c',
            'Lead Conversion Time' => 'Lead_Conversion_Time__c',
            'Lead Creation Date' => 'Lead_Creation_Date__c',
            'Lead Source' => 'Lead_Source__c',
            'Level and Qualification Achieved' => 'Level_and_Qualification_Achieved__c',
            'Message' => 'Message__c',
            'Mobile' => 'Mobile__c',
            'Modified By' => 'Modified_By__c',
            'Modified Time' => 'Modified_Time__c',
            'Occupation' => 'Occupation__c',
            'Other Qualification / Industry Certification' => 'Other_Qualification_Industry_Certifica__c',
            'Other Qualifications Age' => 'Other_Qualifications_Age__c',
            'Other Qualifications Name' => 'Other_Qualifications_Name__c',
            'Payment Method' => 'Payment_Method__c',
            'Payment Option' => 'Payment_Option__c',
            'Payment Plan Serial' => 'Payment_Plan_Serial__c',
            'Payment received' => 'Payment_received__c',
            'Payment Reference Number' => 'Payment_Reference_Number__c',
            'Phone' => 'Phone__c',
            'Portfolio Id' => 'Portfolio_Id__c',
            'Portfolio Name' => 'Name',
            'Portfolio Owner' => 'OwnerId',
            'Portfolio Stage' => 'Portfolio_Stage__c',
            'Post Code' => 'Post_Code__c',
            'Promotion voucher No. if applicable' => 'Promotion_voucher_No_if_applicable__c',
            'Province' => 'Province__c',
            'Qualification Demanded' => 'Qualification_Demanded__c',
            'Qualification Demanded Code' => 'Qualification_Demanded_Code__c',
            'Qualification Price' => 'Qualification_Price__c',
            'Qualification Quoted Price' => 'Qualification_Quoted_Price__c',
            'Re-Enrolled Date' => 'Re_Enrolled_Date__c',
            'Reason For Qualification' => 'Reason_For_Qualification__c',
            'Reason for Refund' => 'Reason_for_Refund__c',
            'Referral Code' => 'Referral_Code__c',
            'Referrer' => 'Referrer__c',
            'Refund Amount' => 'Refund_Amount__c',
            'Refund Notes' => 'Refund_Notes__c',
            'Refund Requested Date' => 'Refund_Requested_Date__c',
            'RPL Completed Date' => 'RPL_Completed_Date__c',
            'Sold by' => 'Sold_by__c',
            'Source Description' => 'Source_Description__c',
            'Specific Source' => 'Specific_Source__c',
            'Specification' => 'Specification__c',
            'Stage' => 'Stage__c',
            'Status' => 'Status__c',
            'Street' => 'Street__c',
            'Suburb' => 'Suburb__c',
            'Target RTO' => 'Target_RTO__c',
            'The Faculty' => 'The_Faculty__c',
            'The Qualification Name' => 'The_Qualification_Name__c',
            'Visitor ID' => 'Visitor_ID__c',
            'Welcome Call Completed' => 'Welcome_Call_Completed__c',
            'Years of Experience' => 'Years_of_Experience__c',
            'SalesforceId' => 'Id'
        ],
        'childObjects' => [
            
        ],
        'parentObjects' => [
            [
                'object' => 'Contact',
                'fields' => [
                    'Client Id' => 'Id'
                ],
                'relations' => [
                    'Client Id' => 'Client__c'
                ]
            ],
            [
                'object' => 'Account',
                'fields' => [
                    'Company Id' => 'Id'
                ],
                'relations' => [
                    'Company Id' => 'Company__c'
                ]
            ],
            [
                'object' => 'User',
                'fields' => [
                    'Created By' => 'Id'
                ],
                'relations' => [
                    'Created By' => 'CreatedById'
                ]
            ],
            [
                'object' => 'Account',
                'fields' => [
                    'Employer' => 'Id'
                ],
                'relations' => [
                    'Employer' => 'Employer__c'
                ]
            ],
            [
                'object' => 'Product2',
                'fields' => [
                    'Qualification Demanded' => 'Id'
                ],
                'relations' => [
                    'Qualification Demanded' => 'Qualification_Demanded__c'
                ]
            ],
            [
                'object' => 'Account',
                'fields' => [
                    'Target RTO' => 'Id'
                ],
                'relations' => [
                    'Target RTO' => 'Target_RTO__c'
                ]
            ]
        ],
        'default' => [
        ]
    ],
    'Leads' => [
        'object' => 'Lead',
        'fields' => [
            'Lead Owner' => '',
            'Active in the system' => '',
            'Ad' => '',
            'Ad Campaign Name' => '',
            'Ad Click Date' => '',
            'Ad Network' => '',
            'Address' => '',
            'AdGroup Name' => '',
            'ADGROUPID' => '',
            'ADID' => '',
            'Applicant Proceeding' => '',
            'Assessor' => '',
            'Australian or Overseas Experience' => '',
            'Australian Work Experience' => '',
            'Business Name' => '',
            'Campaign' => '',
            'Chart Start URL' => '',
            'Chat Transcript' => '',
            'City' => '',
            'Click Type' => '',
            'Client IP' => '',
            'Company' => '',
            'Contact Person' => '',
            'Contact Phone' => '',
            'Conversion Export Status' => '',
            'Conversion Exported On' => '',
            'Cost per Click' => '',
            'Cost per Conversion' => '',
            'Cost Price' => '',
            'Country' => '',
            'Created By' => '',
            'Created Time' => '',
            'Currency' => '',
            'Date of Birth' => '',
            'Date of Enquiry' => '',
            'Date Resume Received' => '',
            'Debit Success Account Num' => '',
            'Deposit Amount' => '',
            'Description' => '',
            'Device Type' => '',
            'Duplicate' => '',
            'Duration' => '',
            'Email' => '',
            'Email Address' => '',
            'Email Opt Out' => '',
            'Employer' => '',
            'Enquiry Time' => '',
            'Exchange Rate' => '',
            'Faculty' => '',
            'Fax' => '',
            'Fax.' => '',
            'Final Quote $' => '',
            'Final Quote Sent' => '',
            'First Name' => '',
            'Followed Up' => '',
            'Full Name' => '',
            'GA Campaign' => '',
            'GA Medium' => '',
            'GA Search Keywords' => '',
            'GA Source' => '',
            'GADCONFIGID' => '',
            'GCLID' => '',
            'Google Click ID' => '',
            'Heard About Us' => '',
            'High Level Skills' => '',
            'Historical Heard About Us' => '',
            'How Did you Hear About GQ?' => '',
            'In-Depth Source' => '',
            'Inbound Source' => '',
            'Interaction ID' => '',
            'Invoice Due Date' => '',
            'Invoice Number' => '',
            'Invoice Paid Date' => '',
            'Invoice Sent' => '',
            'Job Title' => '',
            'Keyword' => '',
            'KEYWORDID' => '',
            'LAMA' => '',
            'Last Activity Time' => '',
            'Last Name' => '',
            'Layout' => '',
            'Layout???' => '',
            'Lead Id' => '',
            'Lead Owner Id' => '',
            'Lead Source' => '',
            'Lead Status' => '',
            'LeadId' => '',
            'Level and Qualification Achieved' => '',
            'Linked to Performance' => '',
            'Mail Address as above' => '',
            'Mailing Postcode' => '',
            'Managing Managers Experience' => '',
            'Message' => '',
            'Mobile' => '',
            'Modified By' => '',
            'Modified Time' => '',
            'Name of Bulk Co. if applicable' => '',
            'Not Proceeding' => '',
            'Occupation' => '',
            'Other Qualifications' => '',
            'Other Qualifications Age' => '',
            'Other Qualifications Name' => '',
            'Payment Method' => '',
            'Payment Option' => '',
            'Payment Plan Serial' => '',
            'Payment Received' => '',
            'Phone' => '',
            'PO Box or Street' => '',
            'Post Code' => '',
            'Potential other Qualifications' => '',
            'Province' => '',
            'Pushed To CS' => '',
            'Qualification Demanded' => '',
            'Qualification Demanded Code' => '',
            'Qualification Level' => '',
            'Qualification Quoted Price' => '',
            'Qualified' => '',
            'Quoted Price' => '',
            'Rating' => '',
            'Read Terms and Cond' => '',
            'Reason for Contact' => '',
            'Reason for Conversion Failure' => '',
            'Reason For Qualification' => '',
            'Referral Code' => '',
            'Referrer' => '',
            'RTO' => '',
            'Sale Price' => '',
            'Salutation' => '',
            'Search Partner Network' => '',
            'Secondary Email' => '',
            'Skill Level' => '',
            'Skype ID' => '',
            'Sold By' => '',
            'Source' => '',
            'Source Description' => '',
            'Special Notes' => '',
            'Specific Source' => '',
            'State' => '',
            'State' => '',
            'Strategic Decision Making' => '',
            'Street' => '',
            'Suburb' => '',
            'Suburb/City/Town' => '',
            'Target RTO' => '',
            'The Faculty' => '',
            'The Qualification Name' => '',
            'Unit Price' => '',
            'Units & Training Package Sent' => '',
            'Using Voucher' => '',
            'Visitor ID' => '',
            'Work Phone' => '',
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
        ],
        'default' => [
        ]
    ],
    'Tasks' => [
        'object' => 'Task',
        'fields' => [
            'Task Owner' => '',
            'Subject' => '',
            'Due Date' => '',
            'Contact Name' => '',
            'Related To' => '',
            'Status' => '',
            'Priority' => '',
            'Created By' => '',
            'Modified By' => '',
            'Closed Time' => '',
            'Created Time' => '',
            'Modified Time' => '',
            'Description' => '',
            'Task Id' => '',
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
        ],
        'default' => [
        ]
    ],
    'Vendors' => [
        'object' => 'Account',
        'fields' => [
            'ACN / ABN' => '',
            'Administration Person Direct Phone' => '',
            'Administration Person First Name' => '',
            'Administration Person Surname' => '',
            'B2B Contact' => '',
            'Business Position' => '',
            'Business Position Title' => '',
            'Business Type' => '',
            'City' => '',
            'Company / Organisation' => '',
            'Company Id' => '',
            'Company Name Abbreviation' => '',
            'Company Name in Full' => '',
            'Company Owner Id' => '',
            'Company Type' => '',
            'Country' => '',
            'Employees' => '',
            'Fax' => '',
            'First Name' => '',
            'Industry' => '',
            'Last Activity Time' => '',
            'Lead Source' => '',
            'Mailing Postcode' => '',
            'Mailing State' => '',
            'Message' => '',
            'Mobile' => '',
            'Number / Unit' => '',
            'Parent Company' => '',
            'Partnership Status' => '',
            'Phone' => '',
            'PO Box or Street' => '',
            'Postcode / ZIP' => '',
            'Province' => '',
            'RTO Provider Code' => '',
            'Specific Source' => '',
            'Street/Road/etc' => '',
            'Suburb/City' => '',
            'Suburb/City/Town' => '',
            'Surname' => '',
            'Title' => '',
            'Trading Name' => '',
            'Website' => '',
            'Work Email' => '',
            'Work Phone' => '',
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
        ],
        'default' => [
            'Company' => 'RecordType'
        ]
    ],
    'Potentials' => [
        'object' => 'Opportunity',
        'fields' => [

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
        ],
        'default' => [
        ]
    ],
    
];
