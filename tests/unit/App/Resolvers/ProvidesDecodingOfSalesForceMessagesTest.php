<?php

use App\Resolvers\ProvidesDecodingOfSalesForceMessages;

class ProvidesDecodingOfSalesForceMessagesTest extends BaseTestCase
{
    use ProvidesDecodingOfSalesForceMessages;

    /** @test */
    public function locateTest()
    {
        $this->runningTestFor(get_class($this));
    }

    /** @test */
    public function it_unserialize_message()
    {
        $expectedOutput = [
            'id' => 'a0Cp0000002BpDVEA0',
            'active' => 'Open',
            'created' => '2016-06-29T14:16:00.000Z',
            'cname' => 'Aaron Hockey',
            'assessor' => '0052800000388EyAAI',
            'cid' => 'zcrm_696292000030905095',
            'stage' => 'Portfolio Submitted To RTO',
            'qual' => '01tp0000002UOnyAAG',
            'qual_amount' => '2700.0',
            'fstage' => 'On Payment Plan',
            'op' => 'changed'
        ];

        $this->assertEquals($expectedOutput, $this->deCodeSalesForceMessage($this->sampleSalesForceMessage()));
    }

    /**
     * @return string
     */
    private function sampleSalesForceMessage()
    {
        return '{"id":"a0Cp0000002BpDVEA0","active":"Open","created":"2016-06-29T14:16:00.000Z","cname":"Aaron Hockey","assessor":"0052800000388EyAAI","cid":"zcrm_696292000030905095","stage":"Portfolio Submitted To RTO","qual":"01tp0000002UOnyAAG","qual_amount":"2700.0","fstage":"On Payment Plan","op":"changed"}';
    }
}
