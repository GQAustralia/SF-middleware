<?php

use Illuminate\Support\Facades\Config;

trait AWSTestHelpers
{
    /**
     * @return string
     */
    public function QUEUE_NAME_SAMPLE()
    {
        return 'SampleQueFromTest';
    }

    /**
     * @return string
     */
    public function QUEUE_NAME_WITH_NO_MESSAGES_SAMPLE()
    {
        return 'SampleQueFromTestWithNoMessages';
    }

    /**
     * @return string
     */
    public function SUCCESS_RESPONSE_SITE()
    {
        return $this->getHostSite() . 'example-response/success';
    }

    /**
     * @return string
     */
    public function UNSUCCESSFUL_RESPONSE_SITE()
    {
        return $this->getHostSite() . 'example-response/failed';
    }

    /**
     * @return string
     */
    public function FORMS_PARAMS_RESPONSE_SITE()
    {
        return $this->getHostSite() . 'example-response/form_params';
    }

    /**
     * @return string
     */
    public function SAMPLE_SALESFORCE_TO_SQS_MESSAGE()
    {
        return "a:11:{s:6:'amount';s:0:'';s:8:'assessor';s:18:'696292000018247009';s:2:'op';s:7:'changed';s:6:'status';s:4:'Open';s:3:'rto';s:5:'31718';s:5:'token';s:20:'fb706b1e933ef01e4fb6';s:2:'mb';s:0:'';s:4:'qual';s:41:'Certificate IV in Training and Assessment';s:4:'cost';s:5:'350.0';s:3:'cid';s:18:'696292000014545306';s:5:'cname';s:11:'Kylie Drost';}";
    }

    /**
     * @return string
     */
    private function getHostSite()
    {
        return Config::get('url.' . env('APP_ENV'));
    }
}