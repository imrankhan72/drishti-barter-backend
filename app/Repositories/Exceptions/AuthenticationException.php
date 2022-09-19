<?php
namespace App\Repositories\Exceptions;
use Exception;

class AuthenticationException extends Exception {
	public function render($request)
	{
		return response()->json('EMAIL_AND_PASSWORD_DO_NOT_MATCH', 401);
	}
}