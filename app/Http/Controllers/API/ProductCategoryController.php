<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\ProductCategory;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;

class ProductCategoryController extends Controller
{
 public function all(Request $request)
 {
    $id = $request->input('id');
    $limit = $request->input('limit');
    $name = $request->input('name');
    $show_category = $request->input('show_category');

    if ($id)
    {
        $category = ProductCategory::with(['category'])->find($id);

        if ($category)
        {
            return ResponseFormatter::success(
                $category,
                'Data kategori berhasil diambil'
            );
        }

        else
        {
            return ResponseFormatter::error(
                null,
                'Data kategori tidak ada',
                404
            );
        }

        
    }

    $category = ProductCategory::query();

        if ($name) {
            $category->where('name', 'like', '%' . $name . '%');
        }

        if ($show_category) {
            $category->with('products');
        }

        return ResponseFormatter::success(
            $category->paginate($limit),
            'Data list kategori berhasil diambil'
        );
 }
}
