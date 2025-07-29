<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class DeleteAccountController extends Controller
{
    use ApiResponseHelpers;

    public function store(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (!Hash::check($request->password, $user->getAuthPassword())) {
            Log::warning('Failed account deletion attempt due to invalid password', ['user_id' => $user->id]);
            return $this->respondError('Invalid password');
        }

        $email = $user->email;
        $userId = $user->id;

        try {
            $user->currentAccessToken()->delete();
            $user->delete();

            Log::info('User account successfully deleted', ['user_id' => $userId, 'email' => $email]);

            return $this->respondWithSuccess([
                'message' => 'Account deleted successfully',
                'email' => $email,
                'status' => 'Account deleted successfully',
                'delete_date' => date('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting user account', ['user_id' => $userId, 'email' => $email, 'error' => $e->getMessage()]);
            return $this->respondError('An error occurred while deleting the account. Please try again.');
        }
    }
}
