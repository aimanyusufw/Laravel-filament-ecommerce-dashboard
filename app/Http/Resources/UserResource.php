<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'is_verified' => (bool) $this->email_verified_at,
            'token' => $this->whenNotNull($this->token),
            'detail' => $this->userDetail !== null ? [
                'profile_picture' => $this->userDetail->profile_picture ? asset(Storage::url($this->userDetail->profile_picture)) : null,
                'billing_name' => $this->userDetail->billing_name,
                'phone' => $this->userDetail->billing_phone,
                'email' => $this->userDetail->billing_email,
                'address' => $this->userDetail->billing_address,
                'province' => [
                    'id' => $this->userDetail->billing_province_id,
                    'name' => $this->userDetail->billing_province_name
                ],
                'city' => [
                    'id' => $this->userDetail->billing_city_id,
                    'name' => $this->userDetail->billing_city_name
                ],
            ] : null
        ];
    }
}
