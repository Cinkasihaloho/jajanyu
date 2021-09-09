<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function all(Request $request){
        $id = $request->input('id');
        $limit = $request->input('limit', 6);
        $status = $request->input('status');

        if($id){
            $transaction = Transaction::with(['items.product'])->find($id);

            if($transaction){
                return ResponseFormatter::success(
                    $transaction,
                    'Data Transaksi berhasil diambil'
                );
            }

            else
            {
                return ResponseFormatter::error(
                null,
                'Data Transaksi tidak ada',
                404
                );
            }
        }

        $transaction = Transaction::with(['items.product'])->where('users_id', Auth::user()->id);

        if($status){
            $transaction->where('status', $status);
        }

        return ResponseFormatter::success(
            $transaction->paginate($limit),
            'Data list transaksi berhasil diambil'
        );
        
    }

    public function checkout(Request $request){

        $request->validate([
            'items' => 'required|array',
            'items.*.id' =>'exists:products,id',
            'total_price' => '',
            'status' => 'required|in:PENDING, SUCCESS, CANCELED, SHIPPING, SHIPPED'
        ]);

        $transaction = Transaction::class([
            'users_id' =>Auth::user()->id,
            'address' =>$request->address,
            'total_price' =>$request->total_price,
            'shipping_price' =>$request->shipping_price,
            'status' =>$request->status,
        ]);

        foreach ($request->item as $product) {
            TransactionItem::create([
                'users_id' =>Auth::user()->id,
                'products_id' =>$product['id'],
                'transactions_id' =>$transaction->id,
                'quantity' =>$product['quantity']
            ]);
        }

        ResponseFormatter::success($transaction->load('items_product'), 'Transaksi berhasil');
    }
}
