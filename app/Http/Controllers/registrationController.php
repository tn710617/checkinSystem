<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class registrationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $this->validate(request(),[
            'name' => 'required|max:255',
            'phone_number' => 'required|regex:/^\(?[+]?\d{1,3}\)?[-\s]?\d{3}[-\s]?\d{3}[-\s]?\d{3}$/',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed|max:255'
             ]);
        $pattenForPhoneNumber = '/[\s\(\)\-\+]/';
        $new_phone_number = preg_replace($pattenForPhoneNumber, '', $request->phone_number);

        $user = User::forceCreate([
            'name' => $request->name,
            'email' =>  $request->email,
            'phone_number' => $new_phone_number,
            'password' => bcrypt($request->password)
        ]);

        Auth::login($user);
        return ["result" => "true", "response" => "You've successfully registered"];

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
