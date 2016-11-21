<?php

use App\Repositories\Eloquent\RepositoryEloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class RepositoryEloquentTest extends BaseTestCase
{
    private $model;

    private $collection;

    public function setUp()
    {
        parent::setUp();

        $this->model = $this->getMockBuilder(Model::class)->getMock();
        $this->collection = $this->app->make(Collection::class);
    }

    /** @test */
    public function locateTest()
    {
        $this->runningTestFor(get_class($this));
    }

    /** @test */
    public function it_returns_model_on_create()
    {
        $repository = Mockery::mock(RepositoryEloquent::class)
            ->shouldReceive('create')
            ->andReturn($this->model)
            ->getMock();

        $this->assertInstanceOf(Model::class, $repository->create([]));
    }

    /** @test */
    public function it_returns_collection()
    {
        $repository = Mockery::mock(RepositoryEloquent::class)
            ->shouldReceive('all')
            ->andReturn($this->collection)
            ->getMock();

        $this->assertInstanceOf(Collection::class, $repository->all());
    }

    /** @test */
    public function it_returns_collection_when_find_all_by_attributes()
    {
        $repository = Mockery::mock(RepositoryEloquent::class)
            ->shouldReceive('findAllBy')
            ->andReturn($this->collection)
            ->getMock();

        $this->assertInstanceOf(Collection::class, $repository->findAllBy('attributeKey', 'attributeValue'));
    }

    /** @test */
    public function it_returns_model_when_find_one_by_attribute()
    {
        $repository = Mockery::mock(RepositoryEloquent::class)
            ->shouldReceive('findBy')
            ->andReturn($this->model)
            ->getMock();

        $this->assertInstanceOf(Model::class, $repository->findBy('attributeKey', 'attributeValue'));
    }
}