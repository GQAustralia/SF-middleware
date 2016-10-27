<?php

use App\Resolvers\ProvidesDecodingOfSalesForceMessages;

class ProvidesDecodingOfSalesForceMessagesTest extends BaseTestCase
{
    use ProvidesDecodingOfSalesForceMessages;

    /** @test */
    public function it_unserialize_message()
    {
        $expectedOutput = [
            'amount' => '',
            'assessor' => 696292000018247009,
            'op' => 'changed',
            'status' => 'Open',
            'rto' => 31718,
            'token' => 'fb706b1e933ef01e4fb6',
            'mb' => '',
            'qual' => 'Certificate IV in Training and Assessment',
            'cost' => 350.0,
            'cid' => '696292000014545306',
            'cname' => 'Kylie Drost'
        ];

        $this->assertEquals($expectedOutput, $this->unSerializeSalesForceMessage($this->sampleSalesForceMessage()));
    }

    /**
     * @return string
     */
    private function sampleSalesForceMessage()
    {
        return '{"id":"a0Cp0000002BpDVEA0","active":"Open","created":"2016-06-29T14:16:00.000Z","cname":"Aaron Hockey","assessor":"0052800000388EyAAI","cid":"zcrm_696292000030905095","stage":"Portfolio Submitted To RTO","qual":"01tp0000002UOnyAAG","qual_amount":"2700.0","fstage":"On Payment Plan","op":"changed"}';
    }
}
