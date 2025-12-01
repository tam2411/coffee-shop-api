<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\DB;

class ProductCategoryController extends Controller
{
    public function index()
    {
        $data = DB::table('product_categories')
            ->join('categories', 'product_categories.category_id', '=', 'categories.id')
            ->select(
        'product_categories.*','categories.name as parent_name'
            )->get();

        return response()->json([
            'ok' => true,
            'data' => $data
        ]);
    }
    //Thêm mới
    public function create(Request $req)
    {
        $pc = ProductCategory::create([
            'name' => $req->name,
            'category_id' => $req->category_id
        ]);

        return response()->json([
            'ok' => true,
            'data' => $pc
        ]);
    }

    // Cập nhật
    public function update(Request $req, $id)
    {
        $pc = ProductCategory::find($id);

        if (!$pc) {
            return response()->json(['ok' => false, 'msg' => 'Not found'], 404);
        }
        $pc->update([
            'name' => $req->name ?? $pc->name,
            'category_id' => $req->category_id ?? $pc->category_id,
        ]);

        return response()->json([
            'ok' => true,
            'data' => $pc
        ]);
    }

    // Xóa
    public function delete($id)
    {
        $pc = ProductCategory::find($id);

        if (!$pc) {
            return response()->json(['ok' => false, 'msg' => 'Not found'], 404);
        }
        $pc->delete();
        return response()->json(['ok' => true]);
    }
}