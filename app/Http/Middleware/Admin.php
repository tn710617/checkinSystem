<?php

namespace App\Http\Middleware;

use App\Token;
use App\User;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if the user is the admin
       $checkAdmin = DB::table('tokens')
           ->where('api_token', $request->token)
           ->join('users', 'users.id', '=', 'tokens.user_id')
           ->select('admin')
           ->first()->admin;

       // if not, deny the access request
        if($checkAdmin != 'yes')
        {
            return response(['result'=>'false', 'response' => 'You are not qualified to access it']);
        }

        // get the permission
        return $next($request);
    }
}
