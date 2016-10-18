<?php

use App\Resolvers\UnserializeSalesForceMessages;

class UnserializeSalesForceMessagesTest extends BaseTestCase
{
    use UnserializeSalesForceMessages;

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
        return "a:11:{s:6:'amount';s:0:'';s:8:'assessor';s:18:'696292000018247009';s:2:'op';s:7:'changed';s:6:'status';s:4:'Open';s:3:'rto';s:5:'31718';s:5:'token';s:20:'fb706b1e933ef01e4fb6';s:2:'mb';s:0:'';s:4:'qual';s:41:'Certificate IV in Training and Assessment';s:4:'cost';s:5:'350.0';s:3:'cid';s:18:'696292000014545306';s:5:'cname';s:11:'Kylie Drost';}";
    }
}