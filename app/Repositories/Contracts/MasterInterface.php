<?php
namespace App\Repositories\Contracts;

interface MasterInterface {
	public function all($columns = array('*'));
	public function create(array $data);
	public function update(array $data, $id);
	public function changeActiveStatus($id);
	public function delete($id);
	public function findById($id);
}