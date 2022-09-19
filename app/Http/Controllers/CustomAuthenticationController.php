<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;
use Hash;
use App\User;
use Validator;

class CustomAuthenticationController extends Controller
{
    public function login(Request $request) {
        $validation= Validator::make($request->all(),[
             'email' => 'required|string|email|max:255',
             'password' => 'required|string|min:6',
        ]);
        if($validation->fails()) {
        	$error = $validation->errors();
        	return response()->json($error,400);
        }
		$user = User::authenticateUser($request['email'], $request['password']);
        $customClaims = ['model_type' => 'users'];
		
		$token = JWTAuth::attempt($request->all());
	    $result['token'] = 'Bearer ' . $token;
	    $result['user'] = $user->load('userGeographies');
	    return response()->json($result,200);
	}
	
	public function logout(Request $request) {
		// dd(JWTAuth::parseToken());
		        $token = $request->header('Authorization');
        // dd($token);
		JWTAuth::invalidate($token);
		return response()->json(true,200); 
	}
	public function testToken() {
       // dd("hello");
		// dd(JWTAuth::parseToken()->authenticate());
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (\Tymon\JWTAuth\Http\Exceptions\TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (\Tymon\JWTAuth\Http\Exceptions\TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (\Tymon\JWTAuth\Http\Exceptions\JWTException $e) {
            return response()->json(['token_absent'], $e->getStatusCode());
        }
        return response()->json($user->load('userGeographies'),201);
		// if (!$user = JWTAuth::parseToken()->authenticate()) {
		//    	throw new \App\Repositories\Exceptions\ModelNotFound;
		// }
		// return response()->json($user,200);
	}
}
