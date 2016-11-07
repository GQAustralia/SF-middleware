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
        return '{"amount":"2177","assessor":"696292000018247009","op":"changed","status":"Open","rto":"31718","token":"fb706b1e933ef01e4fb6","mb":"","qual":"Certificate of blahblah","cost":"350","cid":"696292000014545306","cname":"Kylie Drost"}';
    }

    /**
     * @return string
     */
    public function SAMPLE_SALESFORCE_TO_SQS_MESSAGE_WITHOUT_OP()
    {
        return '{"amount":"2177","assessor":"696292000018247009","status":"Open","rto":"31718","token":"fb706b1e933ef01e4fb6","mb":"","qual":"Certificate of blahblah","cost":"350","cid":"696292000014545306","cname":"Kylie Drost"}';
    }

    /**
     * @return string
     */
    public function SAMPLE_SALESFORCE_TO_SQS_MESSAGE_WITH_INVALID_OP()
    {
        return '{"amount":"2177","assessor":"696292000018247009","op":"invalidOp","status":"Open","rto":"31718","token":"fb706b1e933ef01e4fb6","mb":"","qual":"Certificate of blahblah","cost":"350","cid":"696292000014545306","cname":"Kylie Drost"}';
    }

    /**
     * @return string
     */
    public function SAMPLE_SALESFORCE_TO_SQS_MESSAGE_WITH_BLANK_OP()
    {
        return '{"amount":"2177","assessor":"696292000018247009","op":"","status":"Open","rto":"31718","token":"fb706b1e933ef01e4fb6","mb":"","qual":"Certificate of blahblah","cost":"350","cid":"696292000014545306","cname":"Kylie Drost"}';
    }

    /**
     * @return string
     */
    private function getHostSite()
    {
        return Config::get('url.' . env('APP_ENV'));
    }

    /**
     * @param string $message
     * @return string
     */
    private function extractSQSMessage($message)
    {
        $message = explode('<Message>', $message);
        $message = explode('</Message>', $message[1]);

        return reset($message);
    }
}
