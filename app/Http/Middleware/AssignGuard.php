<?php

namespace App\Http\Middleware;

use Closure;
use App\DrishteeMitra;
use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Tymon\JWTAuth\Exceptions\JWTException;
// use App\Evaluator;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Config;

class AssignGuard extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // return JWTAuth::parseToken();
        // dd($this->auth->setRequest($request)->getToken());
        // dd(JWTAuth::parseToken()->authenticate());
         if (! $token = $this->auth->setRequest($request)->getToken()) {

            // dd('hee');
            return response()->json('token_not_provided', 400);
        }
        try {
                
            // dd($user = JWTAuth::parseToken()->authenticate());

            // AS : Allow Only Evaluator's token to be validated
           // if($user_type == 'evaluators') {
                $id = JWTAuth::parseToken()->getPayload()->get('sub');
                $evaluator = DrishteeMitra::find($id);
                if($evaluator) {
                    return $next($request);
                }
                return response()->json(['error' => 'Evaluator_not_found'], 404);
           // }
            // else 
            // {
            //     return response()->json(['Token Mismatch'], 400);
            // }
          
        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Token Expired'], 400);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid JWT token'], 400);
        }
        if (! $evaluator) {
            return response()->json(['tymon.jwt.user_not_found', 'evaluator_not_found'], 404);
        }
        return $next($request);
    }
}
