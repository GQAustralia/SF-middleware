<?php

trait FacilitatorPerformanceSeederHelper
{
    private $facilitatorPerformanceData = [
        'rto_performance' => [
            [
                'platform_name' => 'facilitator_performance',
                'url' => 'http://52.65.121.177/facilitator-performance/queue_message_receiver_handler.php'
            ]
        ],
        'changed' => [
            [
                'platform_name' => 'facilitator_performance',
                'url' => 'http://52.65.121.177/facilitator-performance/queue_message_receiver_handler.php'
            ]
        ],
        'submitted' => [
            [
                'platform_name' => 'facilitator_performance',
                'url' => 'http://52.65.121.177/facilitator-performance/queue_message_receiver_handler.php'
            ]
        ]
    ];

    public function getFacilitatorPerformanceData()
    {
        return $this->facilitatorPerformanceData;
    }
}
