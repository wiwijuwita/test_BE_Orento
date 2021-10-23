<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use App\User;

class AuthController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function register(Request $request)
    {
        $validasi = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|unique',
            'password' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[0-9])(?=.*[A-Z]).{8,}$/'
        ]);

        if ($validasi->fails()) {
            $data = array(
                'success' => false,
                'message' => $validasi->errors(),
                'data' => NULL
            );
            $code = 400;
        }else{
            $name = $request->input('name');
            $email = $request->input('email');
            $password = Hash::make($request->input('password'));

            $create = User::create([
                'name' => $name,
                'email'  => $email,
                'password' => $password,
            ]);
            
            if ($create) {
                $data  = array(
                    'success'    => true,
                    'message'    => 'Ok',
                    'data'       => $create
                );
                $code = 200;
            }else{
                $data  = array(
                    'success'    => false,
                    'message'    => 'Fail',
                    'data'       => NULL
                );
                $code = 400;
            }
           
        }
        return response()->json($data, $code);       
    }

    public function login(Request $request)
    {
        $validasi = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[0-9])(?=.*[A-Z]).{8,}$/'
        ]);

        if ($validasi->fails()) {
            $data = array(
                'success' => false,
                'message' => $validasi->errors(),
                'data' => NULL
            );
            $code = 400;
        }else{
            $email = $request->input('email');
            $password = $request->input('password');

            $user = User::where('email', $email)->first();
            if ($user) {
                if (Hash::check($password, $user->password)) {              //correct password
                    $apiToken = base64_encode($email.':'.$password);
                    $user->update([
                        'api_token' => $apiToken
                    ]);
                    $user->token = $apiToken;
                    $data = array(
                        'success'   => true,
                        'message'   => 'Ok',
                        'data'      => $user
                    );
                    $code = 200;
                }else{                                                      //incorrect password
                $data = array(
                    'success' => false,
                    'message' => 'Password incorect',
                    'data' => NULL);
                    $code = 400;
                }
            }else{
                $data = array(
                    'success' => false,
                    'message' => 'Username not exist',
                    'data' => NULL);
                $code = 400;
            }
        }
        return response()->json($data, $code);
    }
}
