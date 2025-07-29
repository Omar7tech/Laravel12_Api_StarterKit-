<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use F9Web\ApiResponseHelpers;
use Illuminate\Auth\Events\Registered;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Support\Facades\Log;

class RegisteredUserController extends Controller
{
    use ApiResponseHelpers;
    public function store(RegisterRequest $request)
    {
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            event(new Registered($user));

            $token = $user->createToken('auth_token')->plainTextToken;

            Log::info('New user registered successfully', ['user_id' => $user->id, 'email' => $user->email]);

            return $this->respondWithSuccess([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
                'status' => 'Registration successful',
                'registration_date' => date('Y-m-d H:i:s'),
            ]);

        } catch (\Exception $e) {
            Log::error('Error during user registration', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => 'An unexpected error occurred during registration. Please try again.'
                ],
                $e->getCode() ?? 500
            );
        }
    }
}
