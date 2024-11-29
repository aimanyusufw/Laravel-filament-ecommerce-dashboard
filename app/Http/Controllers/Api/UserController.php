<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateUserDetailRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class UserController extends Controller
{
    // get user
    public function getUser(Request $request)
    {
        return response(['message' => 'Get user successfully', 'data' => new UserResource($request->user()->load('userDetail'))], 200);
    }

    public function updateOrCreateUserDetail(Request $request)
    {
        $data = $request->validate([
            'profile_picture' => 'image|max:5120',
            'billing_name' => 'string|max:50',
            'billing_email' => 'email|max:225',
            'billing_phone' => 'string|numeric|min_digits:10|max_digits:15',
            'billing_address' => 'string|min:10',
            'billing_province_id' => 'exists:provinces,id',
            'billing_city_id' => 'exists:cities,id'
        ]);

        $user = $request->user();

        if ($request->hasFile('profile_picture')) {
            $data['profile_picture'] = $this->storeImage($user, $request->file('profile_picture'));
        }

        $user->userDetail()->updateOrCreate([], $data);

        return response()->json([
            'message' => 'Update user detail successfully',
            'data' => new UserResource($user->load('userDetail'))
        ]);
    }

    public function storeImage($user, $image)
    {
        $userDetail = $user->userDetail;
        if ($userDetail && $userDetail->profile_picture && Storage::disk('public')->exists($userDetail->profile_picture)) {
            Storage::disk('public')->delete($userDetail->profile_picture);
        }
        $file_name = Str::slug(pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME))
            . '-' . time() . '.' . $image->getClientOriginalExtension();
        $canvas = Image::canvas(500, 500);
        $resizedImage = Image::make($image)->resize(null, 500, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        $canvas->insert($resizedImage, 'center');
        $path = 'users/' . $file_name;
        Storage::disk('public')->put($path, (string) $canvas->encode());

        return $path;
    }
}
