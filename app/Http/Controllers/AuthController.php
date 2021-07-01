<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Validator;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(){
        $parameters = request(['email', 'password']);
        $parameters['status'] = 'active';

        $token = auth()->attempt($parameters);

        if(!$token){
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function whoami(){
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(){
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(){
        return $this->respondToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondToken($token, $statusCode = 200){
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function signup(){
        // Exclude data from request
        $parameters = request(['name', 'email', 'password', 'password_confirmation']);

        // Validator
        $validator = Validator::make($parameters, [
            'name' => ['required', 'string', 'min:1', 'max:255'],
            'email' => ['required', 'email', 'unique:users'],
            'password'=> ['required', 'string', 'min:8', 'max:16', 'regex:/^(?=.{8,16}$)[a-zA-Z0-9_.-]+$/', 'confirmed']
        ]);

        // Validator response
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        // Hash password
        $parameters['password'] = Hash::make($parameters['password']);

        // Create in DB
        $newUser = User::create($parameters);

        // Get the token
        $token = auth()->login($newUser);

        // Response
        if(!$token){
            return response()->json($newUser, 201);
        }
        else{
            return $this->respondToken($token, 201);
        }
    }
}