<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Voucher;
class CheckOutController extends Controller
{
    public function checkoutCart(Request $request)
    {
        $userId = Auth::id();

        // Lấy cart item của user
        $cart = CartItem::with('product')
            ->where('user_id', $userId)
            ->get();

        if ($cart->isEmpty()) {
            return response()->json(['ok' => false, 'msg' => 'Giỏ hàng trống'], 400);
        }

        // Tính tiền
        $subtotal = 0;
        $orderItemsData = [];

        foreach ($cart as $item) {
            $unit_price = $item->price_at_time ?? $item->product->price;
            $line_total = $unit_price * $item->quantity;

            $subtotal += $line_total;

            $orderItemsData[] = [
                "product_id" => $item->product_id,
                "product_name_snapshot" => $item->product->name,
                "unit_price" => $unit_price,
                "qty" => $item->quantity,
                "line_total" => $line_total
            ];
        }

        // Xử lý voucher
        [$discount_total, $shipping_fee] = $this->applyVoucher($subtotal, $userId);

        $grand_total = $subtotal - $discount_total + $shipping_fee;

        // Tạo order
        $order = Order::create([
            "order_code" => "OD-" . time(),
            "user_id" => $userId,
            "contact_name" => Auth::user()->full_name,
            "contact_phone" => "0123456789",
            "shipping_address_text" => "Địa chỉ tự lấy từ Address",
            "subtotal" => $subtotal,
            "discount_total" => $discount_total,
            "shipping_fee" => $shipping_fee,
            "grand_total" => $grand_total,
            "payment_method" => $request->payment_method ?? 'COD'
        ]);

        // Lưu order_items
        foreach ($orderItemsData as $oi) {
            $oi["order_id"] = $order->id;
            OrderItem::create($oi);
        }

        // Xóa giỏ hàng
        CartItem::where('user_id', $userId)->delete();

        return response()->json([
            "ok" => true,
            "data" => $order
        ]);
    }

    public function buyNow(Request $request)
    {;
        $userId = Auth::id();
        $product = Product::find($request->product_id);
        $qty = $request->qty;

        // Tính tiền
        $unit_price = $product->price;
        $line_total = $unit_price * $qty;
        $subtotal = $line_total;

        // Xử lý voucher
        [$discount_total, $shipping_fee] = $this->applyVoucher($subtotal, $userId);

        $grand_total = $subtotal - $discount_total + $shipping_fee;

        // Tạo order
        $order = Order::create([
            "order_code" => "OD-" . time(),
            "user_id" => $userId,
            "contact_name" => Auth::user()->full_name,
            "contact_phone" => "0123456789",
            "shipping_address_text" => "Địa chỉ lấy từ Address",
            "subtotal" => $subtotal,
            "discount_total" => $discount_total,
            "shipping_fee" => $shipping_fee,
            "grand_total" => $grand_total,
            "payment_method" => $request->payment_method
        ]);

        // Lưu order_items
        OrderItem::create([
            "order_id" => $order->id,
            "product_id" => $product->id,
            "product_name_snapshot" => $product->name,
            "unit_price" => $unit_price,
            "qty" => $qty,
            "line_total" => $line_total
        ]);

        return response()->json([
            "ok" => true,
            "data" => $order
        ]);
    }


    public function history()
    {
        $orders = Order::where('user_id', Auth::id())
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'ok' => true,
            'data' => $orders,
        ]);
    }
    public function detail($orderId)
    {
        $order = Order::where('id', $orderId)
            ->where('user_id', Auth::id())
            ->with('items')
            ->first();

        if (!$order) {
            return response()->json(['ok' => false, 'msg' => 'Order not found'], 404);
        }

        return response()->json(['ok' => true, 'data' => $order]);
    }
    
    private function applyVoucher($subtotal, $userId)
    {
        $discount_total = 0;
        $shipping_fee = 30000;

        $user = User::find($userId);

        if ($user->voucher_id) {
            $voucher = Voucher::find($user->voucher_id);

            if ($voucher->discount_percent > 0) {
                $discount_total = $subtotal * ($voucher->discount_percent / 100);
            }

            if ($voucher->free_ship) {
                $shipping_fee = 0;
            }
        }

        return [$discount_total, $shipping_fee];
    }



}
