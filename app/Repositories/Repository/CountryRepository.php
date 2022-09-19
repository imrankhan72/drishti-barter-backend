<?php
namespace App\Repositories\Repository;
use App\Repositories\Eloquent\Master;


class CountryRepository extends Master {
	function model()
	{
		return 'App\Country';
	}
	// public function all($columns = array('*'))
	// {
	// 	return $this->model->get()->load('role');
	// }

	// public function createUser($data)
	// {
	// 	$data['password'] = $this->makePassword($data['password']);
    //     return $this->create($data);
	// }
	// public function makePassword($password)
	// {
	// 	return Hash::make($password);
	// }

	// public function updateUser(array $data, $id, $attribute="id")
	// {
	// 	$data['password'] = $this->makePassword($data['password']);
    //     return $this->update($data, $id, $attribute="id");
	// }
}