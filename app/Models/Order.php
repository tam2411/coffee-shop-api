<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'order_code',
        'user_id',
        'contact_name',
        'contact_phone',
        'shipping_address_text',
        'subtotal',
        'discount_total',
        'shipping_fee',
        'grand_total',
        'status',
        'payment_method',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
?>