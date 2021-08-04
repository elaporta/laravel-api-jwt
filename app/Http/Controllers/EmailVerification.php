<?php

namespace App\Http\Controllers;

// Dependencies
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;

class EmailVerification extends Controller
{
    public function notice(){
        if(request()->user()->hasVerifiedEmail()){
            return response()->json(['message' => 'Email already verified'], 200);
        }

        return response()->json(['error' => 'Forbidden', 'message' => 'You must verify your email'], 403);
    }

    public function verify($id, $hash){
        $user = request()->user();

        if($user->id != $id){
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if(request()->user()->hasVerifiedEmail()){
            return response()->json(['message' => 'Email already verified'], 200);
        }

        if($user->markEmailAsVerified()){
            event(new Verified($user));
        }

        return response()->json(['message' => 'Email verified'], 200);
    }

    public function send(){
        $user = request()->user();

        if($user->hasVerifiedEmail()){
            return response()->json(['message' => 'Email already verified'], 200);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Email sent'], 200);
    }
}
