<?php

use App\Repositories\Eloquent\SalesForceLogRepositoryEloquent;
use App\SalesForceLog;
use Illuminate\Support\Collection;

class SalesForceRepositoryEloquentTest extends BaseTestCase
{
    private $repository;

    public function setUp()
    {
        parent::setUp();

        $this->repository = $this->app->make(SalesForceLogRepositoryEloquent::class);
    }

    /** @test */
    public function it_returns_salesforcelog_on_instantiated_model()
    {
        $repository = new SalesForceLogRepositoryEloquent(new SalesForceLog());

        $this->assertInstanceOf(SalesForceLog::class, $repository->model());
    }

    /** @test */
    public function it_returns_salesforcelog_on_create()
    {
        $salesForceLogInput = factory(SalesForceLog::class)->make();

        $result = $this->repository->create($salesForceLogInput->toArray());

        $this->assertInstanceOf(SalesForceLog::class, $result);
        $this->assertAttributesExpectedValues(
            ['object_name', 'message', 'response_body'],
            $salesForceLogInput,
            $result
        );
        $this->seeInDatabase('salesforce_log', [
            'object_name' => $salesForceLogInput->object_name,
            'message' => $salesForceLogInput->message,
            'response_body' => $salesForceLogInput->response_body
        ]);
    }

    /** @test */
    public function it_returns_an_empty_collection_when_no_salesforcelog_exist()
    {
        $result = $this->repository->all();

        $this->assertEmpty($result);
    }

    /** @test */
    public function it_returns_a_collection_of_salesforcelog()
    {
        factory(SalesForceLog::class, 5)->create();

        $result = $this->repository->all();

        $this->assertEquals(5, count($result));
        $this->assertInstanceOf(SalesForceLog::class, $result[0]);
        $this->assertInstanceOf(Collection::class, $result);
    }

    /** @test */
    public function it_returns_a_salesforcelog_when_search_by_attribute()
    {
        $salesForceLog = factory(SalesForceLog::class)->create();

        $result = $this->repository->findBy('id', $salesForceLog->id);

        $this->assertInstanceOf(SalesForceLog::class, $result);
        $this->assertEquals($salesForceLog->message, $result->message);
    }

    /** @test */
    public function it_returns_null_on_searching_salesforcelog_by_attribute_when_no_action_exist()
    {
        $result = $this->repository->findBy('id', 'unknownValue');

        $this->assertNull($result);
    }

    /** @test */
    public function it_returns_a_collection_of_message_when_search_all_by_attribute()
    {
        $initialSalesForceLog = factory(SalesForceLog::class)->create(['object_name' => 'SampleCommonObjectNameErr']);
        $extraSalesForceLog = factory(SalesForceLog::class, 2)->create(['object_name' => 'SampleCommonObjectName']);

        $result = $this->repository->findAllBy('object_name', 'SampleCommonObjectName');

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertInstanceOf(SalesForceLog::class, $result[0]);
        $this->assertEquals(2, count($result));
    }

    /** @test */
    public function it_returns_an_empty_collection_when_search_all_by_attribute()
    {
        $result = $this->repository->findAllBy('id', 'unknownName');

        $this->assertEmpty($result);
        $this->assertInstanceOf(Collection::class, $result);
    }
}