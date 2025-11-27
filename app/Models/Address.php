<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Address extends Model
{
    protected $table = 'addresses';

    protected $fillable = [
        'user_id',
        'label',
        'receiver_name',
        'receiver_phone',
        'address_line',
        'ward',
        'district',
        'province',
        'is_default',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
?>