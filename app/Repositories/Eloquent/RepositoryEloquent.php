<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\RepositoryInterface;
use App\SqsMessage;
use Illuminate\Container\Container as App;
use Illuminate\Database\Eloquent\Model;

abstract class RepositoryEloquent implements RepositoryInterface
{
    /**
     * @var Model
     */
    protected $model;
    /**
     * @var App
     */
    private $app;

    /**
     * RepositoryEloquent constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->model = ($this->app->make($this->model()))->newQuery();
    }

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    abstract function model();

    /**
     * @param array $dataPayload
     * @return SqsMessage
     */
    public function create($dataPayload = [])
    {
        return $this->model()->create($dataPayload);
    }
}