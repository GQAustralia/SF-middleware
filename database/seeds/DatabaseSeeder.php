<?php

use App\Action;
use App\Subscriber;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    const HOST = 'http://52.65.121.177/';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Subscriber::insert($this->subscriberData());

        foreach ($this->getFacilitators() as $facilitator) {
            $action = Action::create(['name' => $facilitator]);
            $action->subscriber()->attach(1);
        }

        foreach ($this->getQualifications() as $facilitator) {
            $action = Action::create(['name' => $facilitator]);
            $action->subscriber()->attach(2);
        }

        foreach ($this->getSalesPerformance() as $facilitator) {
            $action = Action::create(['name' => $facilitator]);
            $action->subscriber()->attach(3);
        }
    }

    private function subscriberData()
    {
        $date = date('Y-m-d');

        return [
            [
                'platform_name' => 'facilitator-performance',
                'url' => self::HOST . 'facilitator-performance/queue_message_receiver_handler.php',
                'created_at' => $date,
                'updated_at' => $date
            ],
            [
                'platform_name' => 'qualification-platform',
                'url' => self::HOST . 'qualification-platform/queue_message_receiver_handler.php',
                'created_at' => $date,
                'updated_at' => $date
            ],
            [
                'platform_name' => 'sales-performance',
                'url' => self::HOST . 'sales-performance/queue_message_receiver_handler.php',
                'created_at' => $date,
                'updated_at' => $date
            ],
            [
                'platform_name' => 'transaction-system-cba',
                'url' => self::HOST . 'transaction-system-cba/queue_message_receiver_handler.php',
                'created_at' => $date,
                'updated_at' => $date
            ]
        ];
    }

    /**
     * @return array
     */
    public function getFacilitators()
    {
        return ['rto_performance', 'log', 'submitted'];
    }

    /**
     * @return array
     */
    public function getQualifications()
    {
        return ['enforce_cost', 'rto_selection'];
    }

    /**
     * @return array
     */
    public function getSalesPerformance()
    {
        return [
            'enforce_rto',
            'not_proceeding',
            'register_sales',
            'rpl_completed',
            'update_amount',
            'update_refund'
        ];
    }
}
