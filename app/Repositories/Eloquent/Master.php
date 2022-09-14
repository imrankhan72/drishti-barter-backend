<?php
namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\MasterInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Container\Container as App;

abstract class Master implements MasterInterface {
	private $app;
	protected $model;

	public function __construct(App $app)
	{
		$this->app = $app;
		$this->makeModel();
	}

	abstract function model();

	public function makeModel()
	{
		$model = $this->app->make($this->model());
		if (!$model instanceof Model)
		    throw new RepositoryException("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
		return $this->model = $model->newQuery();
	}

	/**
	* @param array $columns
	* @return mixed
	*/
    public function all($columns = array('*')) {
        return $this->model->get($columns);
    }
	/**
   * @param array $data
   * @return mixed
   */
  	public function create(array $data) {
      //dd($data);
      return $this->model->create($data);
  	}

  /**
   * @param array $data
   * @param $id
   * @param string $attribute
   * @return mixed
   */
    public function update(array $data, $id, $attribute="id") {
      $result = $this->model->where($attribute, '=', $id)->update($data);
      $model = $this->model->find($id);
      return $model;
  	}
  	public function changeActiveStatus($id)
  	{
  		$model = $this->model->find($id);
  		$model->is_active = !$model->is_active;
  		$model->save();
  		return $model;
  	}

    public function delete($id)
    {
      $model = $this->model->find($id);
      $model->destroy($id);
      return true; 

    }

    public function findById($id)
    {
      return $this->model->find($id);
    }
}