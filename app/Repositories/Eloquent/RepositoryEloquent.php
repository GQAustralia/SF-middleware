<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Container\Container as App;
use Illuminate\Database\Eloquent\Collection;
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
    protected $app;

    /**
     * RepositoryEloquent constructor.
     *
     * @codeCoverageIgnore
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $model = $this->app->make($this->model());
        $this->model = $model->newQuery();
    }

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    abstract public function model();

    /**
     * @param array $dataPayload
     * @return Model
     */
    public function create($dataPayload = [])
    {
        return $this->model()->create($dataPayload);
    }

    /**
     * @return Collection
     */
    public function all()
    {
        return $this->model()->all();
    }

    /**
     * @param string     $attribute
     * @param int|string $value
     * @return Model
     */
    public function findBy($attribute, $value)
    {
        return $this->model()->where($attribute, $value)->first();
    }

    /**
     * @param string     $attribute
     * @param int|string $value
     * @return Collection
     */
    public function findAllBy($attribute, $value)
    {
        return $this->model()->where($attribute, $value)->get();
    }
}
