<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsertDetail extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected static function booted()
    {
        static::saving(function ($userDetail) {
            if ($userDetail->billing_province_id) {
                $userDetail->billing_province_name = Province::find($userDetail->billing_province_id)?->province_name;
            }
            if ($userDetail->billing_city_id) {
                $city = City::find($userDetail->billing_city_id);
                $userDetail->billing_city_name = $city->type . " " . $city->city_name;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'billing_province_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'billing_city_id');
    }
}
