<?php

namespace App\Http\Controllers;

use App\Token;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SessionController extends Controller {

    public function logout(Request $request)
    {
        Token::where('api_token', $request->token)->delete();

        return ['result' => 'true', 'response' => 'You\'ve successfully logged out'];
    }

    //
    public function update(Request $request)
    {
        // Validate required value
        $validator = validator::make($request->all(),
            [
            'name' => 'required|max:255',
            'phone_number' => 'required|regex:/^\(?[+]?\d{1,3}\)?[-\s]?\d{3}[-\s]?\d{3}[-\s]?\d{3}$/',
            'current_password' => 'required|string|min:6|max:255',
            'updated_password' => 'required|string|min:6|max:255'
             ]);
        if ($validator->fails())
        {
            return ['result' => 'false', 'response' => $validator->errors()->first()];
        }
        $user_id = Token::getUserIdThroughToken($request->token);

        $currentBcryptedPasswordInTheDatabase = User::find($user_id)->first()->password;
        if(!hash::check($request->current_password, $currentBcryptedPasswordInTheDatabase))
        {
            return array_merge(
                array('result' => 'false', 'response' => 'Please check your credentials and try again'),
                // If updatedToken does exist, return updatedToken
                (($request->get('updatedToken') !== null) ? array('updatedToken' => $request->get('updatedToken')) : array()));
        }

        // Filter the unnecessary symbols before inserting into database
        $pattenForPhoneNumber = '/[\s\(\)\-\+]/';
        $new_phone_number = preg_replace($pattenForPhoneNumber, '', $request->phone_number);

        User::find($user_id)->update([
            'name' => $request->name,
            'phone_number' => $new_phone_number,
            'password' => bcrypt($request->updated_password)
        ]);

        Token::where('user_id', $user_id)->delete();

        return array_merge(
            array("result" => "true", "response" => "You've successfully update your personal information"),
            // If updatedToken does exist, return updatedToken
            (($request->get('updatedToken') !== null) ? array('updatedToken' => $request->get('updatedToken')) : array()));
    }
}
