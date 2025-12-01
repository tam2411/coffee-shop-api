<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\User;

class AdminController extends Controller
{
    //1. Tổng quan dashboard
    // Tổng doanh thu, tổng đơn, tổng khách, tổng sản phẩm
    public function overview()
    {
        $totalRevenue = Order::where('status', 'COMPLETED')->sum('grand_total');
        $totalOrders  = Order::count();
        $totalUsers   = User::count();

        $totalProducts = DB::table('products')->count();

        return response()->json([
            'ok' => true,
            'data' => [
                'total_revenue'  => $totalRevenue,
                'total_orders'   => $totalOrders,
                'total_users'    => $totalUsers,
                'total_products' => $totalProducts,
            ]
        ]);
    }

    //2. Doanh thu theo tháng
    public function revenueByMonth()
    {
        $data = Order::where('status', 'COMPLETED')
            ->select(
                DB::raw('MONTH(created_at) AS month'),
                DB::raw('SUM(grand_total) AS revenue')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json(['ok' => true, 'data' => $data]);
    }

    //3. Doanh thu theo ngày
    public function revenueByDay()
    {
        $data = Order::where('status', 'COMPLETED')
            ->select(
                DB::raw('DATE(created_at) AS day'),
                DB::raw('SUM(grand_total) AS revenue')
            )
            ->groupBy('day')
            ->orderBy('day')
            ->limit(7)
            ->get();

        return response()->json(['ok' => true, 'data' => $data]);
    }

    //4. Doanh thu theo tuần
    public function revenueByWeek()
    {
        $data = Order::where('status', 'COMPLETED')
            ->select(
                DB::raw('YEARWEEK(created_at) AS week'),
                DB::raw('SUM(grand_total) AS revenue')
            )
            ->groupBy('week')
            ->orderBy('week')
            ->limit(4)
            ->get();

        return response()->json(['ok' => true, 'data' => $data]);
    }

    //5. Tỷ lệ trạng thái đơn hàng
    public function orderStatusRate()
    {
        $data = Order::select(
                'status',
                DB::raw('COUNT(*) AS total')
            )
            ->groupBy('status')
            ->get();

        return response()->json(['ok' => true, 'data' => $data]);
    }

    //6. Doanh thu theo phương thức thanh toán
    public function revenueByPayment()
    {
        $data = Order::where('status', 'COMPLETED')
            ->select(
                'payment_method',
                DB::raw('SUM(grand_total) AS revenue')
            )
            ->groupBy('payment_method')
            ->get();

        return response()->json(['ok' => true, 'data' => $data]);
    }

    //7. Top khách hàng chi nhiều tiền nhất
    public function topCustomers()
    {
        $data = Order::where('status', 'COMPLETED')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->select(
                'users.full_name',
                'users.email',
                DB::raw('SUM(orders.grand_total) AS total_spent')
            )
            ->groupBy('users.id', 'users.full_name', 'users.email')
            ->orderByDesc('total_spent')
            ->limit(10)
            ->get();

        return response()->json(['ok' => true, 'data' => $data]);
    }

    //8. Sản phẩm bán chạy
    public function bestSellingProducts()
    {
        $data = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select(
                'products.name AS product_name',
                DB::raw('SUM(order_items.qty) AS total_sold')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();

        return response()->json(['ok' => true, 'data' => $data]);
    }

    //9. Doanh thu theo từng tháng của 1 năm
    public function revenueByMonthOfYear($year)
    {
        $data = Order::where('status', 'COMPLETED')
            ->whereYear('created_at', $year)
            ->select(
                DB::raw('MONTH(created_at) AS month'),
                DB::raw('SUM(grand_total) AS revenue')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json(['ok' => true, 'data' => $data]);
    }
}
