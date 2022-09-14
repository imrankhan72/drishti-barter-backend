<?php
namespace App\Repositories\Exceptions;
use Exception;

class ModelNotFound extends Exception {
	public function render($request)
	{
		return response()->json('RESOURCE_DOES_NOT_EXISTS', 404);
	}
}
