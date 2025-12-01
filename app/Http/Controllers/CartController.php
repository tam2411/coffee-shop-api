<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
    {
    public function index()
    {
        $userId = Auth::id();

        $cart = CartItem::with('product')
            ->where('user_id', $userId)
            ->orderBy('id', 'asc')
            ->get();

        $total = $this->total($userId);

        return response()->json([
            'ok' => true,
            'data' => $cart,
            'total' => $total,
        ]);
    }

    public function add(Request $request)
    {
        $userId = Auth::id();
        $productId = $request->product_id;
        $quantity = $request->quantity ?? 0;
        $note = $request->note ?? null;

        $product = Product::find($productId);

        $item = CartItem::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($item) {
            $item->update([
                'quantity' => $item->quantity + $quantity,
                'note'     => $note ?? $item->note,
            ]);
        } else {
            $item = CartItem::create([
                'user_id'        => $userId,
                'product_id'     => $productId,
                'quantity'       => $quantity,
                'price_at_time'  => $product->price,
                'note'           => $note,
            ]);
        }
        $total= $this->total($userId);

        return response()->json([
            'ok' => true,
            'data' => $item,
            'total'=> $total,
        ]);
    }

    public function update(Request $request, $id)
    {
        $userId = Auth::id();

        $item = CartItem::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$item) {
            return response()->json(['ok' => false, 'msg' => 'Cart item not found'], 404);
        }

        $item->update([
            'quantity' => $request->quantity ?? $item->quantity,
            'note'     => $request->note ?? $item->note,
        ]);

        // Tính tổng tiền mới
        $total = $this->total($userId);
        return response()->json([
            'ok' => true,
            'data' => $item,
            'total' => $total,
        ]);
    }

    public function delete($id)
    {
        $userId = Auth::id();

        $item = CartItem::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$item) {
            return response()->json(['ok' => false, 'msg' => 'Cart item not found'], 404);
        }

        $item->delete();

        // Lấy lại danh sách mới + total
        $cart = CartItem::with('product')
            ->where('user_id', $userId)
            ->orderBy('id', 'asc')
            ->get();

        $total = $this->total($userId);
        return response()->json([
            'ok' => true,
            'data' => $cart,
            'total' => $total,
        ]);
    }
    public function clear()
    {
        CartItem::where('user_id', Auth::id())->delete();

        return response()->json([
            'ok' => true,
            'data' => [],
            'total' => 0
        ]);
    }

    public function total($userId)
    {
        $cart = CartItem::where('user_id', $userId)->get();
        $i=$cart->count();
        $total =0.00;
        while ($i!=0) {
            $i--;
            $total += $cart[$i]->quantity * $cart[$i]->price_at_time;
        }
        return $total;
    }
}
