<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
class User extends Authenticatable implements JWTSubject
{
    protected $table = 'users';

    protected $fillable = [
        'full_name',
        'email',
        'password_hash',
        'role',
        'is_active',
        'failed_attempts',
        'locked_until',
        'last_login_at',
    ];

    // 🛒 Giỏ hàng mới
    public function cartItems()
    {
        return $this->hasMany(CartItem::class, 'user_id');
    }

    // 🏠 Địa chỉ người dùng
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    // ✉ Chat (đã sửa theo DB của Tâm)
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    // 📦 Đơn hàng
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }
}
