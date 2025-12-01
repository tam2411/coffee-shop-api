<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Support\Facades\Mail;
use App\Mail\VoucherAssignedMail;

class AssignMonthlyVouchers extends Command
{
    protected $signature = 'voucher:assign';
    protected $description = 'Cấp voucher tự động cho top 5 khách hàng mỗi tháng';

    public function handle()
    {
        $month = now()->subMonth()->month+1;
        $this->info("Month: " . $month);
        $year  = now()->subMonth()->year;
        $this->info("Year: " . $year);

        // Lấy top 5 khách hàng tháng trước
        $top = Order::where('status', 'COMPLETED')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->select('user_id', DB::raw('SUM(grand_total) AS spent'))
            ->groupBy('user_id')
            ->orderByDesc('spent')
            ->limit(5)
            ->get();
            
        $this->info("Top size: " . $top->count());

        // Reset voucher toàn bộ user
        User::query()->update(['voucher_id' => null]);

        foreach ($top as $index => $item) {

            if ($index === 0)      $voucherId = 1; // RANK1
            elseif ($index <= 2)  $voucherId = 2; // RANK2
            else                  $voucherId = 3; // RANK3

            $user = User::find($item->user_id);

            if (!$user) continue;

            // Gán voucher cho user
            $user->update([
                'voucher_id' => $voucherId
            ]);

            // Gửi email
            $this->info($user->email);
            Mail::to($user->email)
                ->send(new VoucherAssignedMail($user, Voucher::find($voucherId)));
            
        }
        $this->info("Đã cấp voucher tháng $month/$year cho top 5 thành công!");
        
    }
}
