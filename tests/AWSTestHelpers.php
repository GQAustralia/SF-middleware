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
    public function QUEUE_NAME_WITH_INVALID_MESSAGES_SAMPLE()
    {
        return 'SampleQueFromTestWithInvalidMessages';
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
        return '{"id":"a0Cp0000002BpDVEA0","active":"Open","created":"2016-06-29T14:16:00.000Z","cname":"Aaron Hockey","assessor":"0052800000388EyAAI","cid":"zcrm_696292000030905095","stage":"Portfolio Submitted To RTO","qual":"01tp0000002UOnyAAG","qual_amount":"2700.0","fstage":"On Payment Plan","op":"changed"}';
    }

    /**
     * @return string
     */
    private function getHostSite()
    {
        return Config::get('url.' . env('APP_ENV'));
    }
}
