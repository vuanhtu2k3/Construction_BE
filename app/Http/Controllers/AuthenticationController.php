<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthenticationController extends Controller
{
    public function authenticate(Request $request)
    {
        //Apply Validation
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'false',
                'errors' => $validator->errors()
            ]);
        } else {
            //Authenticate User
            $arrayUser = [
                'email' => $request->email,
                'password' => $request->password,
            ];
            if (Auth::attempt($arrayUser)) {
                $user = User::find(Auth::user()->id);
                $token = $user->createToken('token')->plainTextToken;

                return response()->json([
                    'status' => true,
                    'token' => $token,
                    'id' => Auth::user()->id,
                ]);
            } else {
                return response()->json([
                    'status' => 'false',
                    'message' => 'Invalid Email or Password'
                ]);
            }
        }
    }
    public function logout()
    {
        $user = User::find(Auth::user()->id);
        $user->tokens()->delete();
        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully'
        ]);
    }
}
