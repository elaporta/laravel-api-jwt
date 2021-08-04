<?php

namespace App\Http\Controllers;

// Dependencies
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Validator;
use Illuminate\Auth\Events\Registered;

// Models
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(){
        // Exclude data from request
        $parameters = request(['email', 'password']);
        $parameters['status'] = 'active';

        // Validator
        $validator = Validator::make(request(['email', 'password', 'remember_me']), [
            'email'=> 'required|email',
            'password'=> 'required|string|min:8|regex:/^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?=.*[`!@#$%&*()_{};:,.<>?~])([a-zA-Z0-9`!@#$%&*()_{};:,.<>?~]){8,}$/',
            'remember_me'=>'nullable|boolean'
        ]);

        // Validator response
        if($validator->fails()){
            return response()->json(['error' => 'Bad request', 'message' => $validator->errors()], 400);
        }

        // Set ttl
        $remember_me = request()->remember_me;
        $ttl = $remember_me === true ? config('jwt.refresh_ttl') : config('jwt.ttl');

        // Set token
        $token = auth()->setTTL($ttl)->attempt($parameters);

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
    public function profile(){
        return response()->json(['data' => auth()->user()], 200);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(){
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out'], 200);
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
        ], $statusCode);
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
        if($validator->fails()){
            return response()->json(['error' => 'Bad request', 'message' => $validator->errors()], 400);
        }

        // Hash password
        $parameters['password'] = Hash::make($parameters['password']);

        // Create in DB
        $newUser = User::create($parameters);

        // Fire registered event for email confirmation
		if(config('app.verify_email')){
			event(new Registered($newUser));
		}
		else{
			$newUser->markEmailAsVerified();
		}

        // Get the token
        $token = auth()->login($newUser);

        // Response
        if(!$token){
            return response()->json(['message' => 'Success', 'data' => $newUser], 201);
        }
        else{
            return $this->respondToken($token, 201);
        }
    }

}