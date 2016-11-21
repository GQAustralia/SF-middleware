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
            'Product Code' => 'ProductCode',
            'Product Name' => 'Name',
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
            'Id' => 'Qualifications_Id__c',
            'SalesforceId' => 'Id'
        ],
        'childObjects' => [
            [
                'object' => 'PricebookEntry',
                'fields' => [
                    'Cost Price' => 'Cost_Price__c',
                    'Unit Price' => 'UnitPrice',
                    'Online Price' => 'Online_Price__c',
                    'Id' => 'Zoho_Qualifications_Id__c'
                ],
                'relations' => [
                    "parentId" => 'Product2Id'
                ],
                'default' => [
                    '01s280000089sP6' => 'PriceBook2ID'
                ]
            ]
        ],
        'parentObjects' => [
            [
                'object' => 'Account',
                'relations' => [
                    'RTO Name' => 'Zoho_RTO_Id__c'
                ]
            ]
        ],
        'default' => [
        ],
        'relations' => [
            'Id' => 'Qualifications_Id__c',
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
            'Id' => 'Portfolio_Id__c',
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
        ],
        'relations' => [
            'Id' => 'Portfolio_Id__c',
        ]
    ],
    //    'Leads' => [
    //        'object' => 'Lead',
    //        'fields' => [
    //           'Lead Owner' => 'OwnerId',
    //            'Active in the system' => 'Active_in_the_system__c',
    //            'Applicant Proceeding' => 'Applicant_Proceeding__c',
    //            'Assessor' => 'Assessor__c',
    //            'Australian or Overseas Experience' => 'Australian_or_Overseas_Experience__c',
    //            'Business Name' => 'Business_Name__c',
    //            'Chart Start URL' => 'Chart_Start_URL__c',
    //            'Chat Transcript' => 'Chat_Transcript__c',
    //            'Client IP' => 'Client_IP__c',
    //            'Contact Person' => 'Contact_Person__c',
    //            'Contact Phone' => 'Phone',
    //            'Cost Price' => 'Cost_Price__c',
    //            'Country' => 'Country',
    //            'Created Time' => 'Created_Time__c',
    //            'Debit Success Account Num' => 'Debit_Success_Account_Num__c',
    //            'Deposit Amount' => 'Deposit_Amount__c',
    //            'Description' => 'Description',
    //            'Duration' => 'Duration__c',
    //            'Email' => 'Email',
    //            'Employer' => 'Employer__c',
    //            'Enquiry Time' => 'Enquiry_Time__c',
    //            'Faculty' => 'Faculty__c',
    //            'Final Quote $' => '',
    //            'First Name' => 'FirstName',
    //            'GA Search Keywords' => 'GA_Search_Keywords__c',
    //            'Google Click ID' => 'Google_Click_ID__c',
    //            'Heard About Us' => 'Heard_About_Us__c',
    //            'High Level Skills' => 'High_Level_Skills__c',
    //            'Historical Heard About Us' => 'Historical_Heard_About_Us__c',
    //            'How Did you Hear About GQ?' => 'How_Did_you_Hear_About_GQ__c',
    //            'In-Depth Source' => 'In_Depth_Source__c',
    //            'Invoice Number' => 'Invoice_Number__c',
    //            'Invoice Paid Date' => 'Invoice_Paid_Date__c',
    //            'Invoice Sent' => 'Invoice_Sent__c',
    //            'Keyword' => 'Keyword__c',
    //            'Last Name' => 'LastName',
    //            'Lead Id' => 'Lead_Id__c',
    //            'Lead Source' => 'LeadSource',
    //            'Lead Status' => '',
    //            'Mailing Postcode' => '',
    //            'Managing Managers Experience' => 'Managing_Managers_Experience__c',
    //            'Message' => 'Message__c',
    //            'Mobile' => 'MobilePhone',
    //            'Not Proceeding' => 'Not_Proceeding__c',
    //            'Occupation' => 'Occupation__c',
    //            'Other Qualifications' => 'Other_Qualifications__c',
    //            'Other Qualifications Age' => 'Other_Qualifications_Age__c',
    //            'Payment Plan Serial' => 'Payment_Plan_Serial__c',
    //            'Phone' => 'Phone',
    //            'Post Code' => 'PostalCode',
    //            'Province' => 'Province__c',
    //            'Pushed To CS' => 'Pushed_To_CS__c',
    //            'Qualification Demanded' => 'Qualification_Demanded__c',
    //            'Qualification Demanded Code' => 'Qualification_Demanded_Code__c',
    //            'Qualification Quoted Price' => 'Qualification_Quoted_Price__c',
    //            'Quoted Price' => 'Quoted_Price__c',
    //            'Read Terms and Cond' => 'Read_Terms_and_Cond__c',
    //            'Reason for Contact' => 'Reason_for_Contact__c',
    //            'Reason For Qualification' => 'Reason_For_Qualification__c',
    //            'Referrer' => 'Referrer__c',
    //            'RTO' => 'RTO__c',
    //            'Sale Price' => 'Sale_Price__c',
    //            'Salutation' => 'Salutation',
    //            'Secondary Email' => 'Secondary_Email__c',
    //            'Skill Level' => 'Skill_Level__c',
    //            'Source Description' => 'Source_Description__c',
    //            'Special Notes' => 'Special_Notes__c',
    //            'Specific Source' => 'Specific_Source__c',
    //            'Strategic Decision Making' => '',
    //            'Street' => 'Street',
    //            'Suburb' => '',
    //            'Target RTO' => 'Target_RTO__c',
    //            'The Faculty' => '',
    //            'Unit Price' => 'Unit_Price__c',
    //            'Using Voucher' => 'Using_Voucher__c',
    //            'Visitor ID' => 'Visitor_ID__c',
    //            'Work Phone' => 'Work_Phone__c',
    //            'SalesforceId' => 'Id'
    //        ],
    //        'childObjects' => [
    //            [
    //
    //            ]
    //        ],
    //        'parentObjects' => [
    //            [
    //                'object' => 'User',
    //                'fields' => [
    //                    'Lead Owner' => 'Id'
    //                ],
    //                'relations' => [
    //                    'Lead Owner' => 'OwnerId'
    //                ]
    //            ]
    //
    //        ],
    //        'default' => [
    //        ]
    //    ],
    'Tasks' => [
        'object' => 'Task',
        'fields' => [
            'Task Owner' => 'OwnerId',
            'Subject' => 'Subject',
            'Due Date' => 'Due_Date__c',
            'Contact Name' => 'Contact_Name__c',
            'Related To' => '',
            'Status' => 'Status',
            'Priority' => 'Priority',
            'Created By' => 'CreatedById',
            'Modified By' => 'LastModifiedById',
            'Closed Time' => 'Closed_Time__c',
            'Created Time' => 'Created_Time__c',
            'Modified Time' => 'Modified_Time__c',
            'Description' => 'Description',
            'Task Id' => 'Task_Id__c',
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
            'Vendor Name' => 'Name',
            'Provider Code' => 'Provider_Code__c',
            'Phone' => 'Phone',
            'Website' => 'Website',
            'Email' => 'Email__c',
            'CURRENT' => 'CURRENT__c',
            'Description' => 'Description',
            'Id' => 'Zoho_RTO_Id__c',
            'SalesforceId' => 'Id'
        ],
        'childObjects' => [
            
        ],
        'parentObjects' => [
            
        ],
        'default' => [
            '012p00000008kWxAAI' => 'RecordTypeId'
        ],
        'relations' => [
            'Id' => 'Zoho_RTO_Id__c',
        ]
    ],
    'Potentials' => [
        'object' => 'Enrollment__c',
        'fields' => [
            'Id' => 'Id',
            'Target RTO_ID' => 'Target_RTO__c',
            'SalesforceId' => 'Id'
        ],
        'childObjects' => [
            
        ],
        'parentObjects' => [
            [
                'object' => 'Account',
                'relations' => [
                    'Target RTO_ID' => 'Zoho_RTO_Id__c'
                ]
            ]
        ],
        'default' => [
        ],
        'relations' => [
            
        ]
    ],
    'Leads' => [
        'object' => 'Contact',
        'fields' => [
            'Id' => 'Id',
            'Target RTO_ID' => 'Employer__c',
            'SalesforceId' => 'Id'
        ],
        'childObjects' => [
            
        ],
        'parentObjects' => [
            [
                'object' => 'Account',
                'relations' => [
                    'Target RTO_ID' => 'Zoho_RTO_Id__c'
                ]
            ]
        ],
        'default' => [
        ],
        'relations' => [
        ]
    ],
    
];
