<?php

class BaseTestCase extends TestCase
{
    /**
     * @return mixed|string
     */
    protected function printContent()
    {
        $content = $this->response->getContent();

        $json = json_decode($content);

        if (json_last_error() === JSON_ERROR_NONE) {
            $content = $json;
        }

        return $content;
    }

    /**
     * @return void
     */
    protected function debugContent()
    {
        print_r($this->response->getContent());

        die('end of debug');
    }
}
