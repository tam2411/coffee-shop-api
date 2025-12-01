<?php

namespace App\Mail;

use App\Models\Voucher;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VoucherAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $voucher;
    public $user;

    public function __construct($user, Voucher $voucher)
    {
        $this->user = $user;
        $this->voucher = $voucher;
    }

    public function build()
    {
        return $this->subject('🎁 Bạn nhận được voucher ưu đãi tháng này!')
                    ->view('emails.voucher');
    }
}
