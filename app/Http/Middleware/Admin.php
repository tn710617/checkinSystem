<?php

namespace App\Http\Middleware;

use App\Token;
use App\User;
use Closure;

class Admin
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
        $user_id = Token::where('api_token', $request->token)->first()->user_id;
        $checkAdmin = User::where('id', $user_id)->first()->admin;
        if($checkAdmin != 'yes')
        {
            return response(['result'=>'false', 'response' => 'You are not qualified to access it']);
        }
        return $next($request);
    }
}
