<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\Request;

class DeleteAccountController extends Controller
{
    use ApiResponseHelpers;


    public function store(Request $request){

        $user = $request->user();
        $user->currentAccessToken()->delete();
        $user->delete();
        return $this->respondOk('Account deleted successfully');
    }


}
