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
//        $dataValidate = validator::make($request->all(), ['token' => 'required']);
//        if($dataValidate->fails())
//        {
//            return response(['result' => 'false', 'response' => $dataValidate->errors()->all()[0]]);
//        }
        var_dump($request->token);
       $checkAdmin = DB::table('tokens')
           ->where('api_token', $request->token)
           ->join('users', 'users.id', '=', 'tokens.user_id')
           ->select('admin')
           ->first()->admin;
//        $checkIfExist = Token::where('api_token', $request->token)->first()->exist();
//        $user_id = Token::where('api_token', $request->token)->first()->user_id;
//        $checkAdmin = User::where('id', $user_id)->first()->admin;
//        if($checkAdmin != 'yes')
//        {
//            return response(['result'=>'false', 'response' => 'You are not qualified to access it']);
//        }
        if($checkAdmin != 'yes')
        {
            return response(['result'=>'false', 'response' => 'You are not qualified to access it']);
        }
        return $next($request);
    }
}
