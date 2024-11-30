<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'shipping_detail' => 'array',
        'shipping_address_detail' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItmes()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }
}
