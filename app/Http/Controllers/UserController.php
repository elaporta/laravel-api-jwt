<?php

namespace App\Http\Controllers;

// Dependencies
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Validator;
use Illuminate\Auth\Events\Registered;

// Models
use App\Models\User;

// Scopes
use App\Scopes\NotDeletedScope;

class UserController extends Controller
{
    public function getAll(){
        return $this->getUsers();
    }

    public function getById($id){
        $parameters = ['id' => $id, 'first' => true];

        // Validator
        $validator = Validator::make($parameters, [
            'id' => ['required', 'integer', 'min:1']
        ]);

        // Validator response
        if ($validator->fails()) {
            return response()->json(['error'=> 'Bad request', 'message' => $validator->errors()], 400);
        }

        return $this->getUsers($parameters);
    }

    public function getBy(){
        // Exclude data from request
        $parameters = request(['id', 'name', 'email', 'status', 'role', 'first']);

        // Validator
        $validator = Validator::make($parameters, [
            'id' => ['integer', 'min:1'],
            'name' => ['string', 'min:1', 'max:255'],
            'email' => ['email'],
            'status' => ['string', 'in:active,deleted'],
            'role' => ['string', 'in:admin,client'],
            'first' => ['boolean']
        ]);

        // Validator response
        if ($validator->fails()) {
            return response()->json(['error'=> 'Bad request', 'message' => $validator->errors()], 400);
        }

        return $this->getUsers($parameters);
    }

    protected function getUsers($parameters = []){
        // Get current user
        $user = request()->user();

        // Query builder
        $qb = User::where('id', '!=', $user->id);

        // Set first flag
        $first = false;

        if(isset($parameters['first'])){
            $first = $parameters['first'];
            unset($parameters['first']);
        }

        // If deleted status is present
        if(isset($parameters['status']) && $parameters['status'] == 'deleted'){
            // Remove NotDeletedScope
            $qb->withoutGlobalScope(NotDeletedScope::class);
        }

        // Set where clauses
        foreach($parameters as $key => $value){
            $qb->where($key, $value);
        }

        // Get query
        if($first){
            $data = $qb->first();
        }
        else{
            $data = $qb->get();
        }

        return response()->json(['data' => $data], 200);
    }

    public function create(){
        // Exclude data from request
        $parameters = request(['name', 'email', 'password', 'role']);

        // Validator
        $validator = Validator::make($parameters, [
            'name' => ['required', 'string', 'min:1', 'max:255'],
            'email' => ['required', 'email', 'unique:users'],
            'password'=> ['required', 'string', 'min:8', 'max:16', 'regex:/^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?=.*[`!@#$%&*()_{};:,.<>?~])([a-zA-Z0-9`!@#$%&*()_{};:,.<>?~]){8,}$/'],
            'role' => ['required', 'string', 'in:admin,client']
        ]);

        // Validator response
        if ($validator->fails()) {
            return response()->json(['error'=> 'Bad request', 'message' => $validator->errors()], 400);
        }

        // Hash password
        $parameters['password'] = Hash::make($parameters['password']);

        // Create in DB
        $data = User::create($parameters);

        // Fire registered event for email confirmation
		if(config('app.verify_email')){
	        event(new Registered($data));
		}
		else{
			$data->markEmailAsVerified();
		}

        return response()->json(['message' => 'Success', 'data' => $data], 201);
    }

    public function update(){
        // Exclude data from request
        $parameters = request(['id', 'name', 'email', 'password', 'role']);

        // Validator
        $validator = Validator::make($parameters, [
            'id' => ['required', 'integer', 'min:1'],
            'name' => ['string', 'min:1', 'max:255'],
            'email' => ['email', 'unique:users'],
            'password'=> ['string', 'min:8', 'max:16', 'regex:/^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?=.*[`!@#$%&*()_{};:,.<>?~])([a-zA-Z0-9`!@#$%&*()_{};:,.<>?~]){8,}$/'],
            'role' => ['string', 'in:admin,client']
        ]);

        // Validator response
        if ($validator->fails()) {
            return response()->json(['error'=> 'Bad request', 'message' => $validator->errors()], 400);
        }

        // Hash password
        if(isset($parameters['password'])){
            $parameters['password'] = Hash::make($parameters['password']);
        }

        // Find user
        $data = User::where('id', $parameters['id'])->first();

        if(!isset($data)){
            return response()->json(['error'=> 'Bad request', 'message' => 'User does not exist'], 400);
        }

        // Update in DB
        $data->fill($parameters)->save();

        return response()->json(['message' => 'Success', 'data' => $data], 200);
    }

    public function delete($id){
        $parameters = ['id' => $id, 'status' => 'deleted'];

        // Validator
        $validator = Validator::make($parameters, [
            'id' => ['required', 'integer', 'min:1']
        ]);

        // Validator response
        if ($validator->fails()) {
            return response()->json(['error'=> 'Bad request', 'message' => $validator->errors()], 400);
        }

        // Get current user
        $user = request()->user();

        // Find user
        $data = User::where('id', '!=', $user->id)->where('id', $parameters['id'])->first();

        if(!isset($data)){
            return response()->json(['error'=> 'Bad request', 'message' => 'User does not exist'], 400);
        }

        // Update in DB
        $data->fill($parameters)->save();

        return response()->json(['message' => 'Success'], 200);
    }
}
