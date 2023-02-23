<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Rules\Password;

class UserController extends Controller
{
    public function login(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $credentials = request(['email', 'password']);
            if(!Auth::attempt($credentials)) {
                return ResponseFormatter::error('Unauthotized, 401');
            }

            // FInd user by email
            $user = User::where('email', $request->email)->first();
            if(!Hash::check($request->password, $user->password)) {
                throw new Exception ('Invalid password');
            }

            //Generate token
            $tokenResult = $user->createToken('authToken')->plainTextToken;

            //return response
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Login success');
            
        } catch (Exception $e) {
            return ResponseFormatter::error('Authentication Failed');
        }

    }

    public function register(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', new Password],
            ]);

            // Create user
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                ]);

            // Generate token
                    $tokenResult = $user->createToken('authToken')->plainTextToken;

            // Return response
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Register success');
        } catch (Exception $error) {
            return ResponseFormatter::error($error->getMessage());
        }
    }

    public function logout(Request $request)
    {
        // Revoke token
        $token = $request->user()->currentAccessToken()->delete();

        //Return response
        return ResponseFormatter::success($token, 'Logout success');
    }

    public function fetch(Request $request)
    {
        //Get user
        $user = $request->user();

        //Return response
        return ResponseFormatter::success($user, 'Fetch success');
    }
}
