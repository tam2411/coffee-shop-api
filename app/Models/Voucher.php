<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $table = 'vouchers';

    protected $fillable = [
        'voucher_code',
        'discount_percent',
        'free_shipping',
        'description',
    ];

    public $timestamps = true;

    public function users()
    {
        return $this->hasMany(User::class, 'voucher_id');
    }
}
