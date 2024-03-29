<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    function register()
    {
        try {
            $data = $this->validateData([
                'name' => 'required|max:255',
                'email' => 'required|max:255|unique:users|email',
                'password' => ['required', Password::defaults()],
            ]);
            $user = new User();
            $user->name = request()->name;
            $user->email = request()->email;
            $user->password = Hash::make(request()->password);
            $user->save();

            $expiration = now()->addMinutes(config('sanctum.expiration'));
            $data['expiration'] = $expiration;
            $data['token'] = $user->createToken('ACESSTOKEN', ['expiration' => $expiration],  $expiration)->plainTextToken;
            $data['user'] = $user;
            $data['success'] = true;
        } catch (\Throwable $th) {
            $data['success'] =  false;
            $data['errors'] =  $th . 'error occur in catch';
        }
        return response()->json(['data' => $data]);
    }

    public function login()
    {
        try {
            $data = $this->validateData(['email' => 'required|email', 'password' => ['required', Password::defaults()]]);
            $user = User::where('email', request()->email)->first();
            if ($user && Hash::check(request()->password, $user->password)) {
                $expiration = now()->addMinutes(config('sanctum.expiration'));
                $data['expiration'] = $expiration;
                $data['token'] = $user->createToken('ACESSTOKEN', ['expiration' => $expiration],  $expiration)->plainTextToken;
                $data['user'] = $user;
                $data['success'] = true;
            } else {
                $data['success'] = false;
                $data['errors'] = 'Incorrect email or password.';
            }
        } catch (\Throwable $th) {
            $data['success'] =  false;
            $data['errors'] =  $th . 'error occur in catch';
        }
        return response()->json(['data' => $data]);
    }

    public function logout()
    {
        Auth::user()->currentAccessToken()->delete();
        return response()->json(['message' => 'you are now logout.']);
    }

    public function hehe()
    {
        return response()->json('hehehehe');
    }

    public function validateData(array $rules)
    {

        $validator = Validator::make(request()->all(), $rules);
        if ($validator->fails()) {
            $data['success'] = false;
            $data['errors'] = $validator->errors();
            $fieldNames = $data['errors']->keys();
            $errorMessages = $data['errors']->all();
            $data['field'] = $fieldNames[0];
            $data['message'] = $errorMessages[0];
            return $data;
        }
    }
}