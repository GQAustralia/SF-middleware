<?php

use Illuminate\Database\Seeder;
use App\Action;

class DatabaseSeeder extends Seeder
{
    use FacilitatorPerformanceSeederHelper;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach($this->getFacilitatorPerformanceData() as $action=>$subscribers){
            $action = $this->createAction($action);
            $this->attachSubscribersToAction($action, $subscribers);
        }
    }

    private function createAction($action)
    {
        $actionId = DB::table('action')->insertGetId(['name' => $action]);
        $actionData = DB::table('action')->where('id', $actionId)->first();
        return new Action(json_decode(json_encode($actionData), true));
    }

    private function attachSubscribersToAction(Action $action, $subscribers)
    {
        foreach ($subscribers as $subscriber) {
            $subscriberId = DB::table('subscriber')->insertGetId([
                'platform_name' => $subscriber['platform_name'],
                'url' => $subscriber['url']
            ]);

            $action->subscriber()->attach($subscriberId);
        }
    }
}
