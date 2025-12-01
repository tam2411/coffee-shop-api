<?php
namespace App\Http\Controllers;

use App\Models\Product;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use App\Models\ProductCategory;
use App\Models\Category;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::select(
                'id',
                'name',
                'price',
                'image_url',
                'short_desc',
                'product_category_id'
            )
            ->where('is_active', 1)
            ->get();
        return response()->json([
            'ok' => true,
            'data' => $products
        ]);
    }
    public function show($id)
    {
        $product = Product::select(
                'products.id',
                'products.name',
                'products.price',
                'products.image_url',
                'products.short_desc',
                'products.description',
                'product_categories.name as category',
                'categories.name as categories'
            )
            ->leftJoin('product_categories', 'products.product_category_id', '=', 'product_categories.id')
            ->leftJoin('categories', 'product_categories.category_id', '=', 'categories.id')
            ->where('products.id', $id)
            ->where('products.is_active', 1)
            ->first();

        if (!$product) {
            return response()->json(['ok' => false, 'msg' => 'Product not found'], 404);
        }

        return response()->json([
            'ok' => true,
            'data' => $product
        ]);
    }


    public function create(Request $req)
    {
        $imageUrl = null;
        if ($req->hasFile('image')) {
            $imageUrl = $this->uploadImage($req->file('image'));
        }
        $slug = Str::slug($req->name);
        $i = 1;
        $originalSlug = $slug;
        while (Product::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $i;
            $i++;
        }

        $category = ProductCategory::find($req->product_category_id);
        if (!$category) {
            return response()->json(['ok' => false, 'msg' => 'Category not found'], 404);
        }
        $parent = Category::find($category->category_id);

        $prefix = $this->makeCategorySku($parent ? $parent->name : $category->name);
        $short  = $this->makeNameProductSku($req->name);
        $sku    = $this->makeSKU($prefix, $short);

        $product = Product::create([
            'name' => $req->name,
            'slug' => $slug,
            'sku' => $sku,
            'price' => $req->price,
            'short_desc' => $req->short_desc,
            'description' => $req->description,
            'image_url' => $imageUrl,
            'stock_qty' => $req->stock_qty ?? 0,
            'product_category_id' => $req->product_category_id,
            'is_active' => 1,
        ]);

        return response()->json([
            'ok' => true,
            'msg' => 'Product created successfully',
            'id' => $product->id,
            'slug' => $slug,
            'sku' => $sku,
            'image_url' => $imageUrl
        ]);
    }

    public function update(Request $req, $id)
    {
        $product = Product::find( $id);

        if (!$product) {
            return response()->json([
                'ok' => false,
                'msg' => 'Product not found'
            ], 404);
        }

        $imageUrl = $product->image_url;
        if ($req->hasFile('image')) {
            if ($product->image_url && file_exists(public_path($product->image_url))) {
                unlink(public_path($product->image_url));
            }
            $imageUrl = $this->uploadImage($req->file('image'));
        }

        $slug = $product->slug;
        if ($req->name && $req->name !== $product->name) {
            $slug = Str::slug($req->name);
            $originalSlug = $slug;
            $i = 1;
            while (Product::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                $slug = $originalSlug . '-' . $i;
                $i++;
            }
        }

        $product->update([
            'name' => $req->name ?? $product->name,
            'slug' => $slug,
            'price' => $req->price ?? $product->price,
            'short_desc' => $req->short_desc ?? $product->short_desc,
            'description' => $req->description ?? $product->description,
            'product_category_id' => $req->product_category_id ?? $product->product_category_id,
            'stock_qty' => $req->stock_qty ?? $product->stock_qty,
            'image_url' => $imageUrl
        ]);

        return response()->json([
            'ok' => true,
            'msg' => 'Product updated successfully',
            'image_url' => $imageUrl
        ]);
    }
    public function delete($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                'ok' => false,
                'msg' => 'Product not found'
            ], 404);
        }
        $product->is_active = 0;
        $product->save();

        return response()->json([
            'ok' => true,
            'msg' => 'Product deactivated successfully'
        ]);
    }


    //p1
    private function makeCategorySku($categoryName)
    {
        $map = [
            'Cà phê' => 'CF',
            'Cà phê truyền thống' => 'CF',
            'Trà' => 'TEA',
            'Trà sữa' => 'MT',
            'Sinh tố' => 'SM',
            'Bánh ngọt' => 'CK',
            'Nước ép' => 'JU',
            'Soda' => 'SD',
            'Soda đá xay' => 'SD',
            'Topping' => 'TP',
            'Sữa chua' => 'YC',
        ];
        return $map[$categoryName] ?? 'OT'; //OT = Other
    }
    //p2
    private function makeNameProductSku($productName)
    {
        $clean = Str::ascii($productName);
        $words = preg_split('/\s+/', trim($clean));
        $code = "";
        foreach ($words as $w) {
            $code .= strtoupper(substr($w, 0, 1));
        }
        return $code;
    }

    //Hàm tạo sku p1+p2
    private function makeSKU($categoryPrefix, $shortName)
    {
        $sku = $categoryPrefix . '-' . $shortName;
        $original = $sku;
        $i = 1;
        while (Product::where('sku', $sku)->exists()) {
            $sku = $original . '-' . $i;
            $i++;
        }

        return $sku;
    }
    //Upload image
    private function uploadImage($file)
    {
        $ext = $file->getClientOriginalExtension();
        $filename = 'product_' . time() . '.' . $ext;
        $file->storeAs('products', $filename, 'public');
        return 'storage/products/' . $filename;
    }
}