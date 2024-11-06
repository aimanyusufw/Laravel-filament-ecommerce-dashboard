<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // get user
    public function getUser(Request $request)
    {
        return response(['message' => 'Get user successfully', 'data' => new UserResource($request->user()->load('userDetail'))], 200);
    }
}
