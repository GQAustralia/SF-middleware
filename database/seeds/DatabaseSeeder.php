<?php

use App\Action;
use App\Subscriber;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
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
                'url' => 'http://52.65.121.177/facilitator-performance/queue_message_receiver_handler.php',
                'created_at' => $date,
                'updated_at' => $date
            ],
            [
                'platform_name' => 'qualification-platform',
                'url' => 'http://52.65.121.177/qualification-platform/queue_message_receiver_handler.php',
                'created_at' => $date,
                'updated_at' => $date
            ],
            [
                'platform_name' => 'sales-performance',
                'url' => 'http://52.65.121.177/sales-performance/queue_message_receiver_handler.php',
                'created_at' => $date,
                'updated_at' => $date
            ],
            [
                'platform_name' => 'transaction-system-cba',
                'url' => 'http://52.65.121.177/transaction-system-cba/queue_message_receiver_handler.php',
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
        return ['rto_performance', 'changed', 'submitted'];
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
